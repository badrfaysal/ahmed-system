<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InquiryController extends SystemController
{
    /**
     * عرض شاشة المهام.
     */
    public function index(Request $request)
    {
        $search           = trim((string) $request->input('search', ''));
        $period           = $request->input('period', 'all');
        $fromDate         = $request->input('from_date');
        $toDate           = $request->input('to_date');
        $selectedUserId   = $request->input('user_id');
        $selectedAssignee = trim((string) $request->input('assignee', ''));

        if ($selectedAssignee === '' && $selectedUserId) {
            $selectedAssignee = 'u:' . (int) $selectedUserId;
        }

        [$start, $end] = $this->resolvePeriod($period, $fromDate, $toDate);

        $authUser       = session('auth_user');
        $canManageTasks = $this->canManageTasks($authUser);
        $isAdmin        = $canManageTasks;

        $hasTaskDate       = Schema::hasColumn('customer_inquiries', 'task_date');
        $hasAssignedTo     = Schema::hasColumn('customer_inquiries', 'assigned_to');
        $hasAssignedToName = Schema::hasColumn('customer_inquiries', 'assigned_to_name');

        $applyScope = function ($query) use ($canManageTasks, $authUser, $selectedAssignee, $start, $end, $hasTaskDate, $hasAssignedTo, $hasAssignedToName) {
            if (!$canManageTasks) {
                if ($hasAssignedTo) {
                    $query->where('assigned_to', $authUser->id ?? 0);
                }
            } elseif ($selectedAssignee !== '') {
                if (str_starts_with($selectedAssignee, 'u:')) {
                    if ($hasAssignedTo) {
                        $query->where('assigned_to', (int) substr($selectedAssignee, 2));
                    }
                } elseif (str_starts_with($selectedAssignee, 'n:')) {
                    $name = substr($selectedAssignee, 2);
                    if ($hasAssignedTo && $hasAssignedToName) {
                        $query->whereNull('assigned_to')->where('assigned_to_name', $name);
                    } elseif ($hasAssignedToName) {
                        $query->where('assigned_to_name', $name);
                    }
                } elseif (is_numeric($selectedAssignee)) {
                    if ($hasAssignedTo) {
                        $query->where('assigned_to', (int) $selectedAssignee);
                    }
                }
            }

            if ($start && $end) {
                if ($hasTaskDate) {
                    $query->whereBetween(DB::raw('COALESCE(task_date, DATE(created_at))'), [
                        $start->toDateString(),
                        $end->toDateString(),
                    ]);
                } else {
                    $query->whereBetween('created_at', [$start, $end]);
                }
            }
        };

        $base = DB::table('customer_inquiries');
        $applyScope($base);

        if ($search !== '') {
            $searchCols = ['customer_name', 'inquiry'];
            if ($hasAssignedToName) {
                $searchCols[] = 'assigned_to_name';
            }
            $this->applyArabicSearch($base, $searchCols, $search);
        }

        $pendingQuery = (clone $base)->where('is_contacted', 0);
        if ($hasTaskDate) {
            $pendingQuery->orderByRaw('COALESCE(task_date, DATE(created_at)) DESC');
        }
        $pending = $pendingQuery->orderByDesc('created_at')->orderByDesc('id')->get();

        $done = (clone $base)->where('is_contacted', 1)
            ->orderByDesc('contacted_at')
            ->orderByDesc('id')
            ->get();

        $scope = DB::table('customer_inquiries');
        $applyScope($scope);
        $todayQuery = clone $scope;
        if ($hasTaskDate) {
            $todayQuery->whereRaw('COALESCE(task_date, DATE(created_at)) = ?', [today()->toDateString()]);
        } else {
            $todayQuery->whereDate('created_at', today());
        }
        $stats = [
            'total'   => (clone $scope)->count(),
            'pending' => (clone $scope)->where('is_contacted', 0)->count(),
            'done'    => (clone $scope)->where('is_contacted', 1)->count(),
            'today'   => $todayQuery->count(),
        ];

        $users = DB::table('users')->orderBy('name')->get(['id', 'name']);

        // بناء قائمة الاقتراحات (مستخدمو السيستم + موظفو الموارد البشرية + أسماء سابقة)
        $assigneeSuggestions = $this->buildAssigneeSuggestions($users);

        // بناء قائمة التصفية بالموظف (مستخدمو السيستم + الأسماء المخصصة)
        $customFilterNames = ($hasAssignedTo && $hasAssignedToName)
            ? DB::table('customer_inquiries')
                ->whereNull('assigned_to')
                ->whereNotNull('assigned_to_name')
                ->where('assigned_to_name', '<>', '')
                ->distinct()
                ->orderBy('assigned_to_name')
                ->pluck('assigned_to_name')
            : ($hasAssignedToName
                ? DB::table('customer_inquiries')
                    ->whereNotNull('assigned_to_name')
                    ->where('assigned_to_name', '<>', '')
                    ->distinct()
                    ->orderBy('assigned_to_name')
                    ->pluck('assigned_to_name')
                : collect());

        $filterAssignees = [];
        foreach ($users as $u) {
            $filterAssignees[] = (object) [
                'value' => 'u:' . $u->id,
                'label' => $u->name,
                'is_custom' => false,
            ];
        }
        foreach ($customFilterNames as $cName) {
            $cName = trim((string) $cName);
            if ($cName === '') continue;
            $filterAssignees[] = (object) [
                'value' => 'n:' . $cName,
                'label' => $cName,
                'is_custom' => true,
            ];
        }

        // خريطة أرقام هواتف الموظفين لتسهيل الإرسال المباشر على واتساب عند اختيار موظف محدد
        $employeePhones = $this->buildEmployeePhonesMap();

        // تجميع المهام المعلقة حسب اليوم ثم الموظف حتى لا تتداخل مهام الأيام مع بعضها
        $pendingDays = $this->groupPendingTasksByDayAndAssignee($pending, $employeePhones);

        return view('inquiries', compact(
            'users', 'assigneeSuggestions', 'employeePhones',
            'filterAssignees', 'pendingDays', 'isAdmin', 'canManageTasks', 'pending', 'done', 'stats',
            'search', 'period', 'fromDate', 'toDate', 'selectedUserId', 'selectedAssignee'
        ));
    }

    private function canManageTasks($user = null): bool
    {
        $user = $user ?: session('auth_user');
        return in_array($user->role ?? null, ['admin', 'manager'], true);
    }

    /**
     * إنشاء مهمة أو عدة مهام (بدون عنوان إلزامي، ولموظف بالسيستم أو اسم حر).
     */
    public function store(Request $request)
    {
        $user = session('auth_user');
        if (!$this->canManageTasks($user)) {
            abort(403);
        }

        $defaultAssignedTo   = $request->filled('assigned_to') ? (int) $request->input('assigned_to') : null;
        $defaultAssignedName = trim((string) $request->input('assigned_name', $request->input('assigned_to_name', '')));
        $defaultPhone        = trim((string) $request->input('assigned_phone', ''));

        $tasksToCreate = [];

        // 1) نمط مجموعات الإدخال السريع (موظف أو عدة موظفين مع مهامهم دفعة واحدة)
        if ($request->has('quick_blocks') && is_array($request->input('quick_blocks'))) {
            foreach ($request->input('quick_blocks') as $block) {
                if (!is_array($block)) continue;
                $blockText = trim((string) ($block['text'] ?? ($block['bulk_text'] ?? '')));
                if ($blockText === '') continue;

                $blockAssignedTo   = !empty($block['assigned_to']) ? (int) $block['assigned_to'] : $defaultAssignedTo;
                $blockAssignedName = trim((string) ($block['assigned_name'] ?? ($block['assigned_to_name'] ?? $defaultAssignedName)));
                $blockPhone        = trim((string) ($block['assigned_phone'] ?? ''));

                $lines = preg_split('/\r\n|\r|\n/', $blockText);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') continue;

                    // إزالة الترقيم أو الرموز الاختيارية من أول السطر مثل: 1- أو - أو *
                    $cleaned = preg_replace('/^(\d+[\.\-\)]|\-|\*|•)\s*/u', '', $line);
                    if ($cleaned === '') $cleaned = $line;

                    $tasksToCreate[] = [
                        'title'             => null,
                        'details'           => mb_substr($cleaned, 0, 2000),
                        'raw_assigned_to'   => $blockAssignedTo,
                        'raw_assigned_name' => $blockAssignedName,
                        'assigned_phone'    => $blockPhone !== '' ? $blockPhone : $defaultPhone,
                    ];
                }
            }
        }
        // 2) نمط الإدخال السريع التقليدي (مربع نص مجمع واحد)
        elseif ($request->filled('bulk_text')) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $request->input('bulk_text'));
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;

                // إزالة الترقيم أو الرموز الاختيارية من أول السطر مثل: 1- أو - أو *
                $cleaned = preg_replace('/^(\d+[\.\-\)]|\-|\*|•)\s*/u', '', $line);
                if ($cleaned === '') $cleaned = $line;

                $tasksToCreate[] = [
                    'title'             => null,
                    'details'           => mb_substr($cleaned, 0, 2000),
                    'raw_assigned_to'   => $defaultAssignedTo,
                    'raw_assigned_name' => $defaultAssignedName,
                    'assigned_phone'    => $defaultPhone,
                ];
            }
        }
        // 3) نمط القائمة المفصلة (مهام متعددة - كل مهمة بنصها ومكلّفها)
        elseif ($request->has('tasks') && is_array($request->input('tasks'))) {
            foreach ($request->input('tasks') as $item) {
                if (!is_array($item)) continue;
                $title   = trim((string) ($item['title'] ?? ''));
                $details = trim((string) ($item['details'] ?? ($item['inquiry'] ?? '')));

                if ($details === '' && $title === '') {
                    continue;
                }
                // لو أرسل حقل عنوان فقط لأي سبب نعتبره نص المهمة
                if ($details === '' && $title !== '') {
                    $details = $title;
                    $title = '';
                }

                $itemAssignedTo   = !empty($item['assigned_to']) ? (int) $item['assigned_to'] : $defaultAssignedTo;
                $itemAssignedName = trim((string) ($item['assigned_name'] ?? ($item['assigned_to_name'] ?? $defaultAssignedName)));
                $itemPhone        = trim((string) ($item['assigned_phone'] ?? ''));
                $phone            = $itemPhone !== '' ? $itemPhone : $defaultPhone;

                $tasksToCreate[] = [
                    'title'             => $title !== '' ? mb_substr($title, 0, 255) : null,
                    'details'           => mb_substr($details, 0, 2000),
                    'raw_assigned_to'   => $itemAssignedTo,
                    'raw_assigned_name' => $itemAssignedName,
                    'assigned_phone'    => $phone,
                ];
            }
        }
        // 3) النموذج الفردي (مرن: يعمل بنص المهمة فقط أو مع عنوان)
        elseif ($request->filled('inquiry') || $request->filled('customer_name')) {
            $title   = trim((string) $request->input('customer_name', ''));
            $details = trim((string) $request->input('inquiry', ''));
            if ($details === '' && $title !== '') {
                $details = $title;
                $title = '';
            }
            $tasksToCreate[] = [
                'title'             => $title !== '' ? mb_substr($title, 0, 255) : null,
                'details'           => mb_substr($details, 0, 2000),
                'raw_assigned_to'   => $defaultAssignedTo,
                'raw_assigned_name' => $defaultAssignedName,
                'assigned_phone'    => $defaultPhone,
            ];
        }

        if (empty($tasksToCreate)) {
            return back()->withInput()->with('error', 'يرجى كتابة مهمة واحدة على الأقل.');
        }

        $usersList = DB::table('users')->get(['id', 'name']);

        foreach ($tasksToCreate as &$t) {
            $resolved = $this->resolveAssignee(
                $t['raw_assigned_to'],
                $t['raw_assigned_name'],
                $usersList
            );
            if ($resolved === null) {
                return back()->withInput()->with('error', 'يرجى كتابة أو اختيار الموظف المكلّف بكل مهمة.');
            }
            $t['assigned_to']      = $resolved['id'];
            $t['assigned_to_name'] = $resolved['name'];
        }
        unset($t);

        $taskDateInput = trim((string) $request->input('task_date', ''));
        try {
            $taskDate = $taskDateInput !== ''
                ? \Carbon\Carbon::parse($taskDateInput)->toDateString()
                : today()->toDateString();
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'تاريخ القائمة غير صالح.');
        }

        $hasTaskDate = Schema::hasColumn('customer_inquiries', 'task_date');
        $now = now();
        $rowsToInsert = [];
        foreach ($tasksToCreate as $t) {
            $rowData = [
                'customer_name'     => $t['title'],
                'assigned_to'       => $t['assigned_to'],
                'assigned_to_name'  => $t['assigned_to_name'],
                'assigned_phone'    => $t['assigned_phone'] !== '' ? mb_substr($t['assigned_phone'], 0, 30) : null,
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
            if ($hasTaskDate) {
                $rowData['task_date'] = $taskDate;
            }
            $rowsToInsert[] = $rowData;
        }

        DB::table('customer_inquiries')->insert($rowsToInsert);

        $count = count($rowsToInsert);
        $msg = ($count === 1) ? 'تم إضافة المهمة بنجاح.' : "تم إضافة {$count} مهام بنجاح.";
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
        if (!$this->canManageTasks($user) && (int) ($row->assigned_to ?? 0) !== (int) ($user->id ?? 0)) {
            abort(403);
        }

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
     * تحديث بيانات مهمة (بدون إلزام بالعنوان، ويقبل موظف السيستم أو اسم حر).
     */
    public function update(Request $request, $id)
    {
        if (!$this->canManageTasks()) {
            abort(403);
        }

        $row = DB::table('customer_inquiries')->where('id', $id)->first();
        if (!$row) {
            return back()->with('error', 'المهمة غير موجودة.');
        }

        $request->validate([
            'customer_name'    => 'nullable|string|max:255',
            'assigned_to'      => 'nullable|integer',
            'assigned_to_name' => 'nullable|string|max:255',
            'assigned_name'    => 'nullable|string|max:255',
            'assigned_phone'   => 'nullable|string|max:30',
            'task_date'        => 'nullable|date',
            'inquiry'          => 'required|string|max:2000',
            'contact_notes'    => 'nullable|string|max:2000',
        ]);

        $usersList = DB::table('users')->get(['id', 'name']);
        $assignedToId = $request->filled('assigned_to') ? (int) $request->input('assigned_to') : null;
        $assignedName = $request->input('assigned_to_name', $request->input('assigned_name', ''));

        $resolved = $this->resolveAssignee($assignedToId, $assignedName, $usersList);
        if ($resolved === null) {
            return back()->withInput()->with('error', 'يرجى كتابة أو اختيار الموظف المكلّف بالمهمة.');
        }

        $title = trim((string) $request->input('customer_name', ''));
        $updateData = [
            'customer_name'    => $title !== '' ? mb_substr($title, 0, 255) : null,
            'assigned_to'      => $resolved['id'],
            'assigned_to_name' => $resolved['name'],
            'assigned_phone'   => $request->filled('assigned_phone') ? trim((string) $request->assigned_phone) : null,
            'inquiry'          => trim((string) $request->inquiry),
            'contact_notes'    => $request->contact_notes,
            'updated_at'       => now(),
        ];
        if (Schema::hasColumn('customer_inquiries', 'task_date') && $request->filled('task_date')) {
            $updateData['task_date'] = \Carbon\Carbon::parse($request->task_date)->toDateString();
        }

        DB::table('customer_inquiries')->where('id', $id)->update($updateData);

        return back()->with('success', 'تم حفظ تعديلات المهمة.');
    }

    /**
     * تعديل جماعي لمهام موظف في يوم محدد (تعديل النصوص، تغيير المكلّف أو التاريخ، حذف أو إضافة مهام).
     */
    public function bulkUpdate(Request $request)
    {
        $user = session('auth_user');
        if (!$this->canManageTasks($user)) {
            abort(403);
        }

        $request->validate([
            'original_ids'     => 'required|array|min:1',
            'original_ids.*'   => 'integer',
            'assigned_to'      => 'nullable|integer',
            'assigned_name'    => 'nullable|string|max:255',
            'assigned_to_name' => 'nullable|string|max:255',
            'task_date'        => 'nullable|date',
            'items'            => 'nullable|array',
            'items.*.id'       => 'nullable|integer',
            'items.*.title'    => 'nullable|string|max:255',
            'items.*.details'  => 'nullable|string|max:2000',
            'items.*.deleted'  => 'nullable',
        ]);

        $usersList = DB::table('users')->get(['id', 'name']);
        $assignedToId = $request->filled('assigned_to') ? (int) $request->input('assigned_to') : null;
        $assignedName = $request->input('assigned_name', $request->input('assigned_to_name', ''));
        $resolved = $this->resolveAssignee($assignedToId, $assignedName, $usersList);
        if ($resolved === null) {
            return back()->withInput()->with('error', 'يرجى كتابة أو اختيار الموظف المكلّف بالمهام.');
        }

        $taskDate = null;
        if ($request->filled('task_date')) {
            try {
                $taskDate = \Carbon\Carbon::parse($request->input('task_date'))->toDateString();
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'تاريخ القائمة غير صالح.');
            }
        }

        $originalIds = collect($request->input('original_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $existingRows = DB::table('customer_inquiries')
            ->whereIn('id', $originalIds)
            ->get()
            ->keyBy('id');

        if ($existingRows->isEmpty()) {
            return back()->with('error', 'المهام المطلوب تعديلها غير موجودة.');
        }

        $validOriginalIds = $existingRows->keys()->map(fn ($id) => (int) $id)->all();
        $hasTaskDate = Schema::hasColumn('customer_inquiries', 'task_date');
        $items = $request->input('items', []);

        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $isDeleted = !empty($item['deleted']) && (string) $item['deleted'] !== '0';
            $itemId    = !empty($item['id']) ? (int) $item['id'] : null;
            $details   = trim((string) ($item['details'] ?? ''));

            if (!$isDeleted && $itemId && in_array($itemId, $validOriginalIds, true) && $details === '') {
                return back()->withInput()->with('error', 'لا يمكن ترك نص مهمة موجودة فارغاً. استخدم زر الحذف لإزالة المهمة.');
            }
        }

        $now = now();
        $updatedCount = 0;
        $addedCount   = 0;
        $deletedCount = 0;

        DB::transaction(function () use (
            $items, $validOriginalIds, $existingRows, $resolved, $taskDate, $hasTaskDate, $user, $now,
            &$updatedCount, &$addedCount, &$deletedCount
        ) {
            $keptIds = [];

            foreach ($items as $item) {
                if (!is_array($item)) continue;
                $isDeleted = !empty($item['deleted']) && (string) $item['deleted'] !== '0';
                $itemId    = !empty($item['id']) ? (int) $item['id'] : null;
                $details   = trim((string) ($item['details'] ?? ''));
                $title     = trim((string) ($item['title'] ?? ''));

                if ($isDeleted) {
                    continue;
                }

                if ($itemId && in_array($itemId, $validOriginalIds, true)) {
                    $oldRow = $existingRows->get($itemId);
                    $updateData = [
                        'customer_name'    => $title !== '' ? mb_substr($title, 0, 255) : null,
                        'assigned_to'      => $resolved['id'],
                        'assigned_to_name' => $resolved['name'],
                        'inquiry'          => mb_substr($details, 0, 2000),
                        'updated_at'       => $now,
                    ];
                    if ($hasTaskDate) {
                        $updateData['task_date'] = $taskDate ?? ($oldRow->task_date ?? today()->toDateString());
                    }
                    DB::table('customer_inquiries')->where('id', $itemId)->update($updateData);
                    $keptIds[] = $itemId;
                    $updatedCount++;
                } elseif ($details !== '') {
                    $insertData = [
                        'customer_name'     => $title !== '' ? mb_substr($title, 0, 255) : null,
                        'assigned_to'       => $resolved['id'],
                        'assigned_to_name'  => $resolved['name'],
                        'assigned_phone'    => null,
                        'inquiry'           => mb_substr($details, 0, 2000),
                        'is_contacted'      => 0,
                        'contacted_at'      => null,
                        'contacted_by'      => null,
                        'contacted_by_name' => null,
                        'created_by'        => $user->id ?? null,
                        'created_by_name'   => $user->name ?? 'غير معروف',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                    if ($hasTaskDate) {
                        $insertData['task_date'] = $taskDate ?? today()->toDateString();
                    }
                    DB::table('customer_inquiries')->insert($insertData);
                    $addedCount++;
                }
            }

            $idsToDelete = array_values(array_diff($validOriginalIds, $keptIds));
            if (!empty($idsToDelete)) {
                $deletedCount = DB::table('customer_inquiries')->whereIn('id', $idsToDelete)->delete();
            }
        });

        return back()->with('success', "تم حفظ تعديلات المهام بنجاح (تحديث: {$updatedCount}" . ($addedCount ? "، إضافة: {$addedCount}" : '') . ($deletedCount ? "، حذف: {$deletedCount}" : '') . ').');
    }

    /**
     * حذف مهمة.
     */
    public function destroy($id)
    {
        if (!$this->canManageTasks()) {
            abort(403);
        }
        DB::table('customer_inquiries')->where('id', $id)->delete();
        return back()->with('success', 'تم حذف المهمة بنجاح.');
    }

    /**
     * ربط المكلّف بالمهمة: يطابق مستخدمي السيستم إن وجد، أو يحفظ الاسم الحر مباشرة.
     */
    private function resolveAssignee(?int $assignedToId, ?string $rawName, Collection $usersList): ?array
    {
        $cleanName = trim((string) preg_replace('/\s+/u', ' ', (string) $rawName));
        $usersById = $usersList->keyBy('id');

        if ($assignedToId && $usersById->has($assignedToId)) {
            $u = $usersById->get($assignedToId);
            if ($cleanName === '' || mb_strtolower(trim($u->name), 'UTF-8') === mb_strtolower($cleanName, 'UTF-8')) {
                return ['id' => (int) $u->id, 'name' => $u->name];
            }
        }

        if ($cleanName === '') {
            return null;
        }

        $lowerInput = mb_strtolower($cleanName, 'UTF-8');
        foreach ($usersList as $u) {
            if (mb_strtolower(trim((string) $u->name), 'UTF-8') === $lowerInput) {
                return ['id' => (int) $u->id, 'name' => $u->name];
            }
        }

        // مطابقة موحّدة للحروف العربية (أ/إ/آ -> ا، ى -> ي، ة -> ه) فقط عند التطابق الوحيد
        $normInput = $this->normalizeArabicTerm($cleanName);
        if ($normInput !== '') {
            $normMatches = $usersList->filter(function ($u) use ($normInput) {
                return $this->normalizeArabicTerm((string) $u->name) === $normInput;
            })->values();
            if ($normMatches->count() === 1) {
                $u = $normMatches->first();
                return ['id' => (int) $u->id, 'name' => $u->name];
            }
        }

        return ['id' => null, 'name' => mb_substr($cleanName, 0, 255)];
    }

    /**
     * تجميع قائمة الاقتراحات من مستخدمي السيستم وموظفي HR والأسماء المضافة سابقاً.
     */
    private function buildAssigneeSuggestions(Collection $users): array
    {
        $suggestions = [];
        $seenKeys = [];

        foreach ($users as $u) {
            $key = $this->normalizeAssigneeKey((string) $u->name);
            $seenKeys[$key] = true;
            $suggestions[] = [
                'user_id' => (int) $u->id,
                'name'    => $u->name,
                'source'  => 'user',
            ];
        }

        if (Schema::hasTable('employees')) {
            $hrRows = DB::table('employees')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['name']);
            foreach ($hrRows as $emp) {
                $name = trim((string) $emp->name);
                if ($name === '') continue;
                $key = $this->normalizeAssigneeKey($name);
                if (isset($seenKeys[$key])) continue;
                $seenKeys[$key] = true;
                $suggestions[] = [
                    'user_id' => null,
                    'name'    => $name,
                    'source'  => 'hr',
                ];
            }
        }

        $hasAssignedTo     = Schema::hasColumn('customer_inquiries', 'assigned_to');
        $hasAssignedToName = Schema::hasColumn('customer_inquiries', 'assigned_to_name');

        $previousNames = ($hasAssignedTo && $hasAssignedToName)
            ? DB::table('customer_inquiries')
                ->whereNull('assigned_to')
                ->whereNotNull('assigned_to_name')
                ->where('assigned_to_name', '<>', '')
                ->orderByDesc('id')
                ->pluck('assigned_to_name')
            : ($hasAssignedToName
                ? DB::table('customer_inquiries')
                    ->whereNotNull('assigned_to_name')
                    ->where('assigned_to_name', '<>', '')
                    ->orderByDesc('id')
                    ->pluck('assigned_to_name')
                : collect());

        foreach ($previousNames as $pName) {
            $name = trim((string) $pName);
            if ($name === '') continue;
            $key = $this->normalizeAssigneeKey($name);
            if (isset($seenKeys[$key])) continue;
            $seenKeys[$key] = true;
            $suggestions[] = [
                'user_id' => null,
                'name'    => $name,
                'source'  => 'custom',
            ];
        }

        return $suggestions;
    }

    /**
     * بناء خريطة أرقام هواتف الموظفين من جدول HR لتسهيل المشاركة على واتساب.
     */
    private function buildEmployeePhonesMap(): array
    {
        $map = [];
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'phone')) {
            $rows = DB::table('employees')
                ->whereNotNull('phone')
                ->where('phone', '<>', '')
                ->get(['name', 'phone']);

            foreach ($rows as $emp) {
                $key = $this->normalizeAssigneeKey((string) ($emp->name ?? ''));
                $phone = trim((string) ($emp->phone ?? ''));
                if ($key !== '' && $phone !== '' && !isset($map[$key])) {
                    $map[$key] = $phone;
                }
            }
        }
        return $map;
    }

    /**
     * تجميع المهام المعلقة حسب اليوم أولاً ثم الموظف بداخل كل يوم (في كارت واحد موحّد لكل يوم).
     */
    private function groupPendingTasksByDayAndAssignee(Collection $pending, array $employeePhones = []): Collection
    {
        $dayBuckets = [];

        foreach ($pending as $row) {
            if (!empty($row->task_date)) {
                $dateKey = (string) $row->task_date;
            } elseif (!empty($row->created_at)) {
                $dateKey = \Carbon\Carbon::parse($row->created_at)->toDateString();
            } else {
                $dateKey = today()->toDateString();
            }
            $dayBuckets[$dateKey][] = $row;
        }

        krsort($dayBuckets); // أحدث يوم في الأعلى

        $todayStr     = today()->toDateString();
        $yesterdayStr = today()->subDay()->toDateString();
        $tomorrowStr  = today()->addDay()->toDateString();

        return collect($dayBuckets)->map(function (array $rows, string $dateKey) use ($todayStr, $yesterdayStr, $tomorrowStr, $employeePhones) {
            // ترتيب مهام اليوم الواحد تصاعدياً حسب ترتيب إضافتها
            $ordered = collect($rows)->sortBy('id')->values();

            $assigneeBuckets = [];
            foreach ($ordered as $row) {
                $name = trim((string) ($row->assigned_to_name ?? ''));
                if (!empty($row->assigned_to)) {
                    $groupKey = 'u_' . $row->assigned_to;
                } elseif ($name !== '') {
                    $groupKey = 'n_' . $this->normalizeAssigneeKey($name);
                } else {
                    $groupKey = 'unassigned';
                }

                $rowPhone = trim((string) ($row->assigned_phone ?? ''));

                if (!isset($assigneeBuckets[$groupKey])) {
                    $displayName = $name !== '' ? $name : 'غير محدد';
                    $fallbackPhone = $name !== '' ? ($employeePhones[$this->normalizeAssigneeKey($name)] ?? null) : null;
                    $assigneeBuckets[$groupKey] = [
                        'key'            => $groupKey,
                        'assignee_id'    => $row->assigned_to ? (int) $row->assigned_to : null,
                        'assignee_name'  => $displayName,
                        'assignee_phone' => $rowPhone !== '' ? $rowPhone : $fallbackPhone,
                        'is_system_user' => !empty($row->assigned_to),
                        'tasks'          => collect(),
                    ];
                } elseif (empty($assigneeBuckets[$groupKey]['assignee_phone']) && $rowPhone !== '') {
                    $assigneeBuckets[$groupKey]['assignee_phone'] = $rowPhone;
                }
                $assigneeBuckets[$groupKey]['tasks']->push($row);
            }

            $assignees = collect($assigneeBuckets)->map(fn (array $b) => (object) $b)->values();

            $carbonDay = \Carbon\Carbon::parse($dateKey)->locale('ar');
            $dayName = $carbonDay->translatedFormat('l');
            $formattedDate = $carbonDay->format('Y/m/d');
            $relativeTag = match ($dateKey) {
                $todayStr     => 'اليوم',
                $yesterdayStr => 'أمس',
                $tomorrowStr  => 'غداً',
                default       => null,
            };

            return (object) [
                'date'            => $dateKey,
                'formatted_date'  => $formattedDate,
                'day_name'        => $dayName,
                'date_label'      => $dayName . ' ' . $formattedDate,
                'relative_tag'    => $relativeTag,
                'total_tasks'     => $ordered->count(),
                'total_assignees' => $assignees->count(),
                'assignees'       => $assignees,
            ];
        })->values();
    }

    private function normalizeAssigneeKey(string $name): string
    {
        $clean = trim((string) preg_replace('/\s+/u', ' ', $name));
        $clean = mb_strtolower($clean, 'UTF-8');
        return strtr($clean, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا']);
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
