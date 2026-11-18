<?php
namespace App\Http\Controllers;

use App\Services\ScreenAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    // ════════════════════════════════════════
    // عرض صفحة الدخول
    // ════════════════════════════════════════
    public function showLogin()
    {
        if (session()->has('auth_user')) {
            $home = ScreenAccessService::homeRouteForUser(session('auth_user')) ?? 'treasury.index';
            return redirect()->route($home);
        }
        return view('login');
    }

    // ════════════════════════════════════════
    // معالجة تسجيل الدخول
    // ════════════════════════════════════════
     public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string|min:4',
        ], [
            'email.required'    => 'الاسم أو البريد الإلكتروني إجباري.',
            'password.required' => 'كلمة المرور إجبارية.',
        ]);
 
        $input = trim($request->email);
 
        // البحث بالإيميل أو الاسم فقط (بدون username — العمود مش موجود في DB)
        $user = DB::table('users')
            ->where('email', $input)
            ->orWhere('name', $input)
            ->first();
 
        if (!$user) {
            return back()->withErrors(['email' => 'الاسم أو البريد غير موجود في النظام.'])->withInput();
        }
 
        if (!$user->is_active) {
            return back()->withErrors(['email' => 'هذا الحساب موقوف. تواصل مع الإدارة.'])->withInput();
        }
 
        if (!Hash::check($request->password, $user->password)) {
            DB::table('activity_logs')->insert([
                'user_id'     => $user->id,
                'user_name'   => $user->name,
                'user_role'   => $user->role,
                'action'      => 'login',
                'module'      => 'auth',
                'description' => "❌ محاولة دخول فاشلة لـ: {$user->name}",
                'ip_address'  => $request->ip(),
                'is_read'     => 0,
                'created_at'  => now(),
            ]);
            return back()->withErrors(['email' => 'كلمة المرور غير صحيحة.'])->withInput();
        }
 
        // ══ تسجيل دخول ناجح ══
        session(['auth_user' => $user]);
 
        DB::table('users')->where('id', $user->id)->update(['last_login' => now()]);
 
        DB::table('activity_logs')->insert([
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'user_role'   => $user->role,
            'action'      => 'login',
            'module'      => 'auth',
            'description' => "✅ تم تسجيل دخول: {$user->name} | دور: {$user->role}",
            'ip_address'  => $request->ip(),
            'user_agent'  => substr($request->userAgent() ?? '', 0, 200),
            'is_read'     => 0,
            'created_at'  => now(),
        ]);

        $home = ScreenAccessService::homeRouteForUser($user);
        if (!$home) {
            session()->forget('auth_user');
            return back()->withErrors(['email' => 'لا توجد شاشات مصرح لك بالدخول عليها. تواصل مع الإدارة.'])->withInput();
        }

        return redirect()->route($home);
    }

    // ════════════════════════════════════════
    // تسجيل الخروج
    // ════════════════════════════════════════
    public function logout(Request $request)
    {
        $user = session('auth_user');

        if ($user) {
            DB::table('activity_logs')->insert([
                'user_id'     => $user->id,
                'user_name'   => $user->name,
                'user_role'   => $user->role,
                'action'      => 'logout',
                'module'      => 'auth',
                'description' => "👋 تم تسجيل الخروج: {$user->name}",
                'ip_address'  => $request->ip(),
                'is_read'     => 0,
                'created_at'  => now(),
            ]);
        }

        session()->forget('auth_user');
        return redirect()->route('login');
    }

    // ════════════════════════════════════════
    // إدارة المستخدمين (للأدمن فقط)
    // الإدارة الفعلية بقت في تاب "إدارة الموظفين" بصفحة الإعدادات (لا يوجد view مستقل لها)
    // ════════════════════════════════════════
    public function users()
    {
        $this->requireAdmin();
        return redirect()->route('settings.index');
    }

    public function storeUser(Request $request)
    {
        $this->requireAdmin();
        if (function_exists('ensureUserScreenPermissionsSchema')) {
            ensureUserScreenPermissionsSchema();
        }
 
        $request->validate([
            'name'            => 'required|string|min:3',
            'email'           => 'required|email',
            'password'        => 'required|string|min:6',
            'role'            => 'required|in:admin,manager,employee,viewer',
            'screen_mode'     => 'nullable|in:default,custom',
            'allowed_screens' => 'nullable|array',
        ], [
            'name.required'     => 'الاسم إجباري.',
            'name.min'          => 'الاسم لازم يكون 3 حروف على الأقل.',
            'email.required'    => 'البريد الإلكتروني إجباري.',
            'email.email'       => 'البريد الإلكتروني غير صحيح.',
            'password.required' => 'كلمة المرور إجبارية.',
            'password.min'      => 'كلمة المرور لازم تكون 6 حروف على الأقل.',
            'role.required'     => 'الدور إجباري.',
        ]);
 
        // تحقق من التكرار بالإيميل أو الاسم فقط (بدون username)
        $exists = DB::table('users')
            ->where('email', $request->email)
            ->orWhere('name', $request->name)
            ->exists();
 
        if ($exists) {
            return back()->withInput()->with('error', 'البريد الإلكتروني أو الاسم مستخدم بالفعل لمستخدم آخر.');
        }

        $screenMode = $request->input('screen_mode', 'default');
        $allowedScreensValue = null;
        if ($screenMode === 'custom') {
            $cleanScreens = $this->sanitizeAllowedScreens(
                $request->role,
                $request->input('allowed_screens', []),
                false
            );
            if (empty($cleanScreens)) {
                return back()->withInput()->with('error', 'يجب اختيار شاشة واحدة على الأقل عند تفعيل التحديد المخصص للشاشات.');
            }
            $allowedScreensValue = json_encode($cleanScreens, JSON_UNESCAPED_UNICODE);
        }

        $insertData = [
            'name'       => trim($request->name),
            'email'      => strtolower(trim($request->email)),
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'is_active'  => 1,
            'created_at' => now(),
        ];
        if (Schema::hasColumn('users', 'allowed_screens')) {
            $insertData['allowed_screens'] = $allowedScreensValue;
        }
 
        DB::table('users')->insert($insertData);
        ScreenAccessService::clearCache();
 
        $admin = session('auth_user');
        DB::table('activity_logs')->insert([
            'user_id'     => $admin->id,
            'user_name'   => $admin->name,
            'user_role'   => $admin->role,
            'action'      => 'create',
            'module'      => 'settings',
            'description' => "👤 تم إنشاء مستخدم جديد: {$request->name} | دور: {$request->role}",
            'is_read'     => 0,
            'created_at'  => now(),
        ]);
 
        return back()->with('success', "تم إضافة المستخدم ({$request->name}) بنجاح.");
    }
 

    public function toggleUser($id)
    {
        $this->requireAdmin();
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) return back()->withInput()->with('error', 'المستخدم غير موجود.');

        $newStatus = $user->is_active ? 0 : 1;
        DB::table('users')->where('id', $id)->update(['is_active' => $newStatus]);
        ScreenAccessService::clearCache();

        $admin = session('auth_user');
        $statusText = $newStatus ? 'تفعيل' : 'إيقاف';
        DB::table('activity_logs')->insert([
            'user_id'     => $admin->id,
            'user_name'   => $admin->name,
            'user_role'   => $admin->role,
            'action'      => 'update',
            'module'      => 'settings',
            'description' => "🔧 {$statusText} مستخدم: {$user->name}",
            'is_read'     => 0,
            'created_at'  => now(),
        ]);

        return back()->with('success', "{$statusText} المستخدم بنجاح.");
    }

    // ════════════════════════════════════════
    // تفعيل/إلغاء إخفاء الأرقام المالية الحساسة عن موظف معيّن
    // (رأس المال/السيولة/الأرباح/منظومة الأقساط في الداشبورد والخزانة وشاشة الأقساط)
    // ════════════════════════════════════════
    public function toggleFinancials($id)
    {
        $this->requireAdmin();
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) return back()->with('error', 'المستخدم غير موجود.');

        $newValue = $user->hide_financials ? 0 : 1;
        DB::table('users')->where('id', $id)->update(['hide_financials' => $newValue]);
        ScreenAccessService::clearCache();

        $admin = session('auth_user');
        $statusText = $newValue ? 'تفعيل إخفاء الأرقام المالية عن' : 'إلغاء إخفاء الأرقام المالية عن';
        DB::table('activity_logs')->insert([
            'user_id'     => $admin->id,
            'user_name'   => $admin->name,
            'user_role'   => $admin->role,
            'action'      => 'update',
            'module'      => 'settings',
            'description' => "🔒 {$statusText} الموظف: {$user->name}",
            'is_read'     => 0,
            'created_at'  => now(),
        ]);

        return back()->with('success', $newValue
            ? "تم إخفاء الأرقام المالية عن ({$user->name})."
            : "تم إظهار الأرقام المالية لـ ({$user->name}) مرة أخرى.");
    }

    public function resetPassword(Request $request, $id)
    {
        $this->requireAdmin();
        $request->validate(['new_password' => 'required|string|min:6']);
        DB::table('users')->where('id', $id)->update([
            'password' => Hash::make($request->new_password),
        ]);
        return back()->with('success', 'تم إعادة تعيين كلمة المرور بنجاح.');
    }
    //اا

    public function updateUser(Request $request, $id)
    {
        $this->requireAdmin();
        $authUser = session('auth_user');
        if ($authUser->id == $id) {
            return back()->with('error', 'لا يمكنك تعديل بياناتك الخاصة من هنا.');
        }

        $request->validate([
            'name' => 'required|string|min:3',
            'role' => 'required|in:admin,manager,employee,viewer',
        ]);

        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) return back()->with('error', 'المستخدم غير موجود.');

        $newName = trim($request->name);

        $exists = DB::table('users')->where('name', $newName)->where('id', '!=', $id)->exists();
        if ($exists) {
            return back()->with('error', 'الاسم مستخدم بالفعل لمستخدم آخر.');
        }

        $updateData = [
            'name' => $newName,
            'role' => $request->role,
        ];

        // لو تم تغيير الدور من أدمن لدور أقل، نحذف الشاشات الإدارية فقط من القائمة المخصصة إن وُجدت
        if ($request->role !== 'admin' && Schema::hasColumn('users', 'allowed_screens') && !empty($user->allowed_screens)) {
            $decoded = json_decode((string) $user->allowed_screens, true);
            if (is_array($decoded)) {
                $filtered = $this->sanitizeAllowedScreens($request->role, $decoded, false);
                $updateData['allowed_screens'] = !empty($filtered)
                    ? json_encode($filtered, JSON_UNESCAPED_UNICODE)
                    : null;
            }
        }

        DB::table('users')->where('id', $id)->update($updateData);
        ScreenAccessService::clearCache();

        DB::table('activity_logs')->insert([
            'user_id'     => $authUser->id,
            'user_name'   => $authUser->name,
            'user_role'   => $authUser->role,
            'action'      => 'update',
            'module'      => 'settings',
            'description' => "🔑 تم تعديل بيانات المستخدم ({$user->name}) ← الاسم: ({$newName}) | الصلاحية: ({$user->role}) ← ({$request->role})",
            'is_read'     => 0,
            'created_at'  => now(),
        ]);

        return back()->with('success', "تم تحديث بيانات ({$newName}) بنجاح.");
    }

    /**
     * حفظ الشاشات المسموح بها لمستخدم أو موظف معيّن.
     */
    public function updateUserScreens(Request $request, $id)
    {
        $this->requireAdmin();
        if (function_exists('ensureUserScreenPermissionsSchema')) {
            ensureUserScreenPermissionsSchema();
        }

        $request->validate([
            'screen_mode'     => 'required|in:default,custom',
            'allowed_screens' => 'nullable|array',
        ]);

        $targetUser = DB::table('users')->where('id', $id)->first();
        if (!$targetUser) {
            return back()->with('error', 'المستخدم غير موجود.');
        }

        $admin = session('auth_user');
        $isSelf = (int) ($admin->id ?? 0) === (int) $targetUser->id;
        $screenMode = $request->input('screen_mode', 'default');

        if ($screenMode === 'default') {
            $newValue = null;
            $desc = "🖥️ إرجاع الشاشات المتاحة لـ ({$targetUser->name}) إلى الوضع الافتراضي حسب الدور";
            $msg = "تم إرجاع الشاشات المتاحة لـ ({$targetUser->name}) للوضع الافتراضي.";
        } else {
            $cleanScreens = $this->sanitizeAllowedScreens(
                (string) $targetUser->role,
                $request->input('allowed_screens', []),
                $isSelf
            );
            if (empty($cleanScreens)) {
                return back()->withInput()->with('error', 'يجب اختيار شاشة واحدة على الأقل عند تحديد الشاشات المخصصة.');
            }
            $newValue = json_encode($cleanScreens, JSON_UNESCAPED_UNICODE);
            $labels = collect($cleanScreens)
                ->map(fn ($k) => ScreenAccessService::screens()[$k]['label'] ?? $k)
                ->implode('، ');
            $desc = "🖥️ تحديد شاشات ({$targetUser->name}): {$labels}";
            $msg = "تم حفظ صلاحيات الشاشات لـ ({$targetUser->name}) (" . count($cleanScreens) . " شاشة).";
        }

        DB::table('users')->where('id', $id)->update([
            'allowed_screens' => $newValue,
            'updated_at'      => now(),
        ]);
        ScreenAccessService::clearCache();

        if ($isSelf) {
            $freshAdmin = DB::table('users')->where('id', $id)->first();
            if ($freshAdmin) {
                session(['auth_user' => $freshAdmin]);
            }
        }

        DB::table('activity_logs')->insert([
            'user_id'     => $admin->id,
            'user_name'   => $admin->name,
            'user_role'   => $admin->role,
            'action'      => 'update',
            'module'      => 'settings',
            'description' => $desc,
            'is_read'     => 0,
            'created_at'  => now(),
        ]);

        return back()->with('success', $msg);
    }

    private function sanitizeAllowedScreens(string $role, array $rawScreens, bool $isSelfAdmin): array
    {
        $catalog = ScreenAccessService::screens();
        $clean = [];
        foreach ($rawScreens as $key) {
            $key = (string) $key;
            if (!isset($catalog[$key])) continue;
            if (!empty($catalog[$key]['admin_only']) && $role !== 'admin') continue;
            if (!in_array($key, $clean, true)) {
                $clean[] = $key;
            }
        }
        if ($isSelfAdmin && $role === 'admin' && !in_array('settings', $clean, true)) {
            $clean[] = 'settings';
        }
        return $clean;
    }

    public function destroyUser($id)
    {
        $this->requireAdmin();
        $authUser = session('auth_user');
        if ($authUser->id == $id) {
            return back()->withInput()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }
        $user = DB::table('users')->where('id', $id)->first();
        DB::table('users')->where('id', $id)->delete();
        DB::table('activity_logs')->insert([
            'user_id'   => $authUser->id,
            'user_name' => $authUser->name,
            'user_role' => $authUser->role,
            'action'    => 'delete',
            'module'    => 'settings',
            'description' => "🗑️ تم حذف المستخدم: {$user->name}",
            'is_read'   => 0,
            'created_at'=> now(),
        ]);
        return back()->with('success', 'تم حذف المستخدم نهائياً.');
    }

    // ════════════════════════════════════════
    // تعديل الاسم الشخصي (لأي مستخدم)
    // ════════════════════════════════════════
    public function updateProfile(Request $request)
    {
        $authUser = session('auth_user');
        if (!$authUser) abort(403);

        $request->validate([
            'name' => 'required|string|min:3|max:80',
        ], [
            'name.required' => 'الاسم مطلوب.',
            'name.min'      => 'الاسم لازم يكون 3 حروف على الأقل.',
        ]);

        $newName = trim($request->name);

        // تحقق من عدم التكرار مع مستخدم آخر
        $exists = DB::table('users')
            ->where('name', $newName)
            ->where('id', '!=', $authUser->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'الاسم ده موجود لمستخدم تاني، اختار اسم مختلف.');
        }

        DB::table('users')->where('id', $authUser->id)->update([
            'name'       => $newName,
            'updated_at' => now(),
        ]);

        // تحديث الـ session فوراً
        $freshUser = DB::table('users')->where('id', $authUser->id)->first();
        session(['auth_user' => $freshUser]);

        DB::table('activity_logs')->insert([
            'user_id'     => $authUser->id,
            'user_name'   => $newName,
            'user_role'   => $authUser->role,
            'action'      => 'update',
            'module'      => 'settings',
            'description' => "✏️ تغيير الاسم: {$authUser->name} → {$newName}",
            'is_read'     => 0,
            'created_at'  => now(),
        ]);

        return back()->with('success', "تم تغيير اسمك إلى ({$newName}) بنجاح.");
    }

    // ── Helper: تحقق من الأدمن ──
    private function requireAdmin()
    {
        if (!session()->has('auth_user') || session('auth_user')->role !== 'admin') {
            abort(403, 'غير مصرح لك بالدخول لهذه الصفحة.');
        }
    }
}