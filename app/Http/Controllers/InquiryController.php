<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InquiryController extends SystemController
{
    /**
     * عرض شاشة المهام.
     */
    public function index(Request $request)
    {
        $search         = trim((string) $request->input('search', ''));
        $period         = $request->input('period', 'all');
        $fromDate       = $request->input('from_date');
        $toDate         = $request->input('to_date');
        $selectedUserId = $request->input('user_id');

        [$start, $end] = $this->resolvePeriod($period, $fromDate, $toDate);

        $authUser = session('auth_user');
        $isAdmin  = ($authUser->role ?? null) === 'admin';

        $base = DB::table('customer_inquiries');
        if (!$isAdmin) {
            $base->where('assigned_to', $authUser->id ?? 0);
        } elseif ($selectedUserId) {
            $base->where('assigned_to', (int) $selectedUserId);
        }

        if ($search !== '') {
            $this->applyArabicSearch($base, ['customer_name', 'assigned_to_name', 'inquiry'], $search);
        }

        if ($start && $end) {
            $base->whereBetween('created_at', [$start, $end]);
        }

        $pending = (clone $base)->where('is_contacted', 0)
            ->orderByDesc('created_at')->get();

        $done = (clone $base)->where('is_contacted', 1)
            ->orderByDesc('contacted_at')->get();

        // إحصائيات على نطاق الفلتر الحالي
        $scope = DB::table('customer_inquiries');
        if (!$isAdmin) {
            $scope->where('assigned_to', $authUser->id ?? 0);
        } elseif ($selectedUserId) {
            $scope->where('assigned_to', (int) $selectedUserId);
        }
        if ($start && $end) {
            $scope->whereBetween('created_at', [$start, $end]);
        }
        $stats = [
            'total'   => (clone $scope)->count(),
            'pending' => (clone $scope)->where('is_contacted', 0)->count(),
            'done'    => (clone $scope)->where('is_contacted', 1)->count(),
            'today'   => (clone $scope)->whereDate('created_at', today())->count(),
        ];

        $users = DB::table('users')->orderBy('name')->get(['id', 'name']);

        // أحدث أرقام هواتف مسجلة لكل موظف لتعبئتها تلقائياً عند الاختيار
        $latestPhones = DB::table('customer_inquiries')
            ->whereNotNull('assigned_to')
            ->whereNotNull('assigned_phone')
            ->where('assigned_phone', '<>', '')
            ->orderByDesc('id')
            ->get(['assigned_to', 'assigned_phone'])
            ->unique('assigned_to')
            ->pluck('assigned_phone', 'assigned_to')
            ->toArray();

        return view('inquiries', compact(
            'users', 'latestPhones', 'isAdmin', 'pending', 'done', 'stats',
            'search', 'period', 'fromDate', 'toDate', 'selectedUserId'
        ));
    }

    /**
     * إنشاء مهمة أو عدة مهام وتكليف الموظف بها.
     */
    public function store(Request $request)
    {
        $user = session('auth_user');
        if (($user->role ?? null) !== 'admin') {
            abort(403);
        }

        $defaultAssignedTo = $request->input('assigned_to');
        $defaultPhone      = $request->input('assigned_phone');

        $tasksToCreate = [];

        // 1) نمط الإدخال السريع (كل سطر مهمة)
        if ($request->filled('bulk_text')) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $request->input('bulk_text'));
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;

                // إزالة الترقيم أو الرموز من أول السطر مثل: 1- أو - أو *
                $cleaned = preg_replace('/^(\d+[\.\-\)]|\-|\*|•)\s*/u', '', $line);
                if ($cleaned === '') $cleaned = $line;

                // إمكانية فصل العنوان والتفاصيل بنقطتين (:) أو شرطة
                $parts = preg_split('/\s*[:\-—]\s*/u', $cleaned, 2);
                $title = trim($parts[0] ?? $cleaned);
                $details = (isset($parts[1]) && trim($parts[1]) !== '') ? trim($parts[1]) : $cleaned;

                $tasksToCreate[] = [
                    'title'          => mb_substr($title, 0, 255),
                    'details'        => mb_substr($details, 0, 2000),
                    'assigned_to'    => $defaultAssignedTo,
                    'assigned_phone' => $defaultPhone,
                ];
            }
        }
        // 2) نمط القائمة المفصلة (مهام متعددة)
        elseif ($request->has('tasks') && is_array($request->input('tasks'))) {
            foreach ($request->input('tasks') as $item) {
                $title = trim($item['title'] ?? '');
                if ($title === '') continue;
                $details = trim($item['details'] ?? '');
                if ($details === '') {
                    $details = $title;
                }
                $assignedTo = !empty($item['assigned_to']) ? (int) $item['assigned_to'] : (int) $defaultAssignedTo;
                $phone      = (isset($item['assigned_phone']) && trim($item['assigned_phone']) !== '') 
                                ? trim($item['assigned_phone']) 
                                : $defaultPhone;

                $tasksToCreate[] = [
                    'title'          => mb_substr($title, 0, 255),
                    'details'        => mb_substr($details, 0, 2000),
                    'assigned_to'    => $assignedTo,
                    'assigned_phone' => $phone,
                ];
            }
        }
        // 3) النموذج الفردي التقليدي
        elseif ($request->filled('customer_name')) {
            $title = trim($request->input('customer_name'));
            $details = trim($request->input('inquiry', ''));
            if ($details === '') {
                $details = $title;
            }
            $tasksToCreate[] = [
                'title'          => mb_substr($title, 0, 255),
                'details'        => mb_substr($details, 0, 2000),
                'assigned_to'    => (int) $defaultAssignedTo,
                'assigned_phone' => $defaultPhone,
            ];
        }

        if (empty($tasksToCreate)) {
            return back()->withInput()->with('error', 'يرجى كتابة مهمة واحدة على الأقل.');
        }

        // جلب أسماء الموظفين للتحقق وتخزين الاسم
        $userIds = array_filter(array_unique(array_column($tasksToCreate, 'assigned_to')));
        $usersMap = DB::table('users')->whereIn('id', $userIds)->pluck('name', 'id');

        foreach ($tasksToCreate as $t) {
            if (empty($t['assigned_to']) || !isset($usersMap[$t['assigned_to']])) {
                return back()->withInput()->with('error', 'يرجى اختيار الموظف المكلّف بالمهام.');
            }
        }

        $now = now();
        $rowsToInsert = [];
        foreach ($tasksToCreate as $t) {
            $rowsToInsert[] = [
                'customer_name'     => $t['title'],
                'assigned_to'       => $t['assigned_to'],
                'assigned_to_name'  => $usersMap[$t['assigned_to']] ?? 'غير معروف',
                'assigned_phone'    => $t['assigned_phone'] ?: null,
                'inquiry'           => $t['details'],
                'is_contacted'      => 0,
                'contacted_at'      => null,
                'contacted_by'      => null,
                'contacted_by_name' => null,
                'created_by'        => $user->id ?? null,
                'created_by_name'   => $user->name ?? 'غير معروف',
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        DB::table('customer_inquiries')->insert($rowsToInsert);

        $count = count($rowsToInsert);
        $msg = ($count === 1) ? 'تم إنشاء المهمة بنجاح.' : "تم إنشاء {$count} مهام بنجاح.";
        return back()->with('success', $msg);
    }

    /**
     * تأكيد التواصل / إرجاع لـ "لم يتم".
     */
    public function toggleContact(Request $request, $id)
    {
        $row = DB::table('customer_inquiries')->where('id', $id)->first();
        if (!$row) {
            return back()->withInput()->with('error', 'المهمة غير موجودة.');
        }

        $user = session('auth_user');

        if ($row->is_contacted) {
            DB::table('customer_inquiries')->where('id', $id)->update([
                'is_contacted'      => 0,
                'contacted_at'      => null,
                'contacted_by'      => null,
                'contacted_by_name' => null,
                'updated_at'        => now(),
            ]);
            return back()->with('success', 'تم إرجاع المهمة إلى قائمة المعلقة.');
        }

        DB::table('customer_inquiries')->where('id', $id)->update([
            'is_contacted'      => 1,
            'contacted_at'      => now(),
            'contacted_by'      => $user->id ?? null,
            'contacted_by_name' => $user->name ?? 'غير معروف',
            'contact_notes'     => $request->input('contact_notes'),
            'updated_at'        => now(),
        ]);

        return back()->with('success', 'تم تأكيد إنجاز المهمة بنجاح.');
    }

    /**
     * تحديث بيانات استفسار.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'assigned_to'    => 'required|integer',
            'assigned_phone' => 'nullable|string|max:30',
            'inquiry'        => 'required|string|max:2000',
            'contact_notes'  => 'nullable|string|max:2000',
        ]);

        if ((session('auth_user')->role ?? null) !== 'admin') {
            abort(403);
        }
        $assignee = DB::table('users')->where('id', $request->assigned_to)->first();
        if (!$assignee) {
            return back()->with('error', 'الموظف المحدد غير موجود.');
        }

        DB::table('customer_inquiries')->where('id', $id)->update([
            'customer_name'    => $request->customer_name,
            'assigned_to'      => $assignee->id,
            'assigned_to_name' => $assignee->name,
            'assigned_phone'   => $request->assigned_phone,
            'inquiry'          => $request->inquiry,
            'contact_notes'    => $request->contact_notes,
            'updated_at'       => now(),
        ]);

        return back()->with('success', 'تم حفظ تعديلات المهمة.');
    }

    /**
     * حذف استفسار.
     */
    public function destroy($id)
    {
        if ((session('auth_user')->role ?? null) !== 'admin') {
            abort(403);
        }
        DB::table('customer_inquiries')->where('id', $id)->delete();
        return back()->with('success', 'تم حذف المهمة بنجاح.');
    }

    private function resolvePeriod(string $period, ?string $from, ?string $to): array
    {
        switch ($period) {
            case 'today':      return [now()->startOfDay(), now()->endOfDay()];
            case 'yesterday':  return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()];
            case 'week':       return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
            case 'month':      return [now()->subDays(29)->startOfDay(), now()->endOfDay()];
            case 'this_month': return [now()->startOfMonth(), now()->endOfMonth()];
            case 'custom':
                if ($from && $to) {
                    return [\Carbon\Carbon::parse($from)->startOfDay(), \Carbon\Carbon::parse($to)->endOfDay()];
                }
                return [null, null];
            case 'all':
            default:
                return [null, null];
        }
    }
}
