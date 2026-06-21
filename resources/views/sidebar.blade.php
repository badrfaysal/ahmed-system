@php
    $unreadCount = \Illuminate\Support\Facades\DB::table('activity_logs')->where('is_read', 0)->count();
    $currentRoute = request()->route()?->getName() ?? '';
    $isAdmin = session('auth_user')?->role === 'admin';

    // ── SVG Icon Library (Lucide-style, inline — لا تعتمد على CDN) ──
    $svgWrap = fn($p) => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">'.$p.'</svg>';
    $icons = [
        'building'   => $svgWrap('<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/>'),
        'user'       => $svgWrap('<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'),
        'home'       => $svgWrap('<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'),
        'gauge'      => $svgWrap('<path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/>'),
        'pie'        => $svgWrap('<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>'),
        'bar'        => $svgWrap('<path d="M3 3v18h18"/><path d="M7 16V8"/><path d="M11 16V11"/><path d="M15 16v-5"/><path d="M19 16V6"/>'),
        'cart'       => $svgWrap('<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2 3h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57L22 7H5.12"/>'),
        'card'       => $svgWrap('<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>'),
        'box'        => $svgWrap('<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>'),
        'fuel'       => $svgWrap('<line x1="3" x2="15" y1="22" y2="22"/><line x1="4" x2="14" y1="9" y2="9"/><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"/><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2 2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"/>'),
        'safe'       => $svgWrap('<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M12 9v1"/><path d="M12 14v1"/><path d="M15 12h-1"/><path d="M10 12H9"/>'),
        'wallet'     => $svgWrap('<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>'),
        'file'       => $svgWrap('<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/>'),
        'receipt'    => $svgWrap('<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/>'),
        'target'     => $svgWrap('<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>'),
        'handshake'  => $svgWrap('<path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"/><path d="m3 3 8.41 8.41"/><path d="M3 3v14l4 4"/>'),
        'calendar'   => $svgWrap('<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="m9 16 2 2 4-4"/>'),
        'trend-up'   => $svgWrap('<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>'),
        'trend-down' => $svgWrap('<polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/>'),
        'users'      => $svgWrap('<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'),
        'contacts'   => $svgWrap('<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/><circle cx="11" cy="11" r="2"/><path d="M14 16a3 3 0 0 0-6 0"/>'),
        'phone'      => $svgWrap('<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.33 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>'),
        'car'        => $svgWrap('<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 9.5l-1.7-3.4A2 2 0 0 0 14.5 5h-9A2 2 0 0 0 3.7 6.1L2 9.5l-1.5.6A2 2 0 0 0 -.5 12v3c0 .6.4 1 1 1h2"/><circle cx="6.5" cy="17.5" r="2.5"/><circle cx="16.5" cy="17.5" r="2.5"/>'),
        'message'    => $svgWrap('<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>'),
        'bell'       => $svgWrap('<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>'),
        'crown'      => $svgWrap('<path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/>'),
        'briefcase'  => $svgWrap('<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>'),
        'shield'     => $svgWrap('<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>'),
        'settings'   => $svgWrap('<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>'),
        'logout'     => $svgWrap('<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>'),
        'chev-left'  => $svgWrap('<polyline points="15 18 9 12 15 6"/>'),
        'chev-right' => $svgWrap('<polyline points="9 18 15 12 9 6"/>'),
        'menu'       => $svgWrap('<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>'),
    ];
@endphp

<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<style>
/* ══════════════════════════════════════════════
   SIDEBAR — نظام إدارة شركة الضبع
   ══════════════════════════════════════════════ */
:root {
    --sb-accent:      #60a5fa;
    --sb-accent-soft: rgba(96, 165, 250, 0.15);
    --sb-gold:        #60a5fa; /* backwards compat */
    --sb-width: 286px;
    --sb-bg: #0d1f35;
    --sb-border: rgba(255, 255, 255, 0.06);
    --sb-transition: .25s ease;
}

.app-sidebar, .app-sidebar * {
    font-family: 'IBM Plex Sans Arabic', 'Tajawal', 'Segoe UI', sans-serif;
}

/* ── تعديل المحتوى الرئيسي ── */
body { overflow-x: hidden; }
.main-content {
    margin-right: var(--sb-width) !important;
    padding: 30px 30px 40px 30px !important;
    width: calc(100% - var(--sb-width)) !important;
    transition: margin-right var(--sb-transition), width var(--sb-transition);
}

/* ── الشريط الجانبي ── */
.app-sidebar {
    position: fixed;
    top: 0;
    right: 0;
    width: var(--sb-width);
    height: 100vh;
    background: var(--sb-bg);
    border-left: 1px solid var(--sb-border);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: width var(--sb-transition), transform var(--sb-transition);
    box-shadow: -4px 0 30px rgba(0, 0, 0, 0.5);
}

/* ── رأس السايد بار ── */
.sb-header {
    padding: 20px 18px 15px;
    border-bottom: 1px solid var(--sb-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    background: rgba(255,255,255,0.02);
}
.sb-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}
.sb-brand-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}
.sb-brand-text h6 {
    font-weight: 600;
    font-size: 1rem;
    color: #fff;
    margin: 0;
    line-height: 1.2;
}
.sb-brand-text span {
    font-size: 0.76rem;
    color: var(--sb-gold);
    font-weight: 500;
}

/* ── زر الطي (Collapse) ── */
.sb-toggle-btn {
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--sb-border);
    color: #94a3b8;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--sb-transition);
    flex-shrink: 0;
}
.sb-toggle-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }

/* ── معلومات المستخدم ── */
.sb-user-info {
    padding: 14px 18px;
    border-bottom: 1px solid var(--sb-border);
    background: rgba(255,255,255,0.01);
    flex-shrink: 0;
}
.sb-user-badge {
    display: flex;
    align-items: center;
    gap: 10px;
}
.sb-user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: rgba(96, 165, 250, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    color: var(--sb-accent);
    flex-shrink: 0;
    border: 1px solid rgba(96, 165, 250, 0.3);
}
.sb-user-name {
    font-size: 0.92rem;
    font-weight: 600;
    color: #f1f5f9;
    line-height: 1.2;
}
.sb-user-role {
    font-size: 0.75rem;
    color: var(--sb-gold);
    font-weight: 500;
}

/* ── محتوى السايد بار القابل للتمرير ── */
.sb-body {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
}
/* فاصل خفيف بين مجموعات الروابط (بدل العناوين) */
.sb-group-sep { height: 1px; background: var(--sb-border); margin: 7px 14px; }
.sb-body::-webkit-scrollbar { width: 4px; }
.sb-body::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

/* ── عناوين الأقسام ── */
.sb-section-title {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #5a6478;
    padding: 18px 18px 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sb-section-title i { color: var(--sb-accent); font-size: 0.76rem; }

/* ── روابط التنقل ── */
.sb-nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 16px;
    color: #94a3b8;
    text-decoration: none !important;
    font-size: 0.88rem;
    font-weight: 500;
    border-radius: 0;
    transition: var(--sb-transition);
    position: relative;
    border-left: 3px solid transparent;
    margin: 0;
}
.sb-nav-link:hover {
    color: #e2e8f0;
    background: rgba(255,255,255,0.05);
    border-left-color: rgba(255,255,255,0.2);
}
.sb-nav-link.active {
    color: #fff;
    background: rgba(96, 165, 250, 0.10);
    border-left-color: var(--sb-accent);
}
.sb-nav-link.active .sb-nav-icon { background: rgba(96, 165, 250, 0.20); color: var(--sb-accent); }

/* ── أيقونات التنقل ── */
.sb-nav-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: var(--sb-transition);
}
.sb-nav-icon svg { width: 16px; height: 16px; stroke-width: 2; }
.sb-section-title svg { width: 13px; height: 13px; stroke-width: 2.2; }
.sb-brand-icon svg, .sb-user-avatar svg { width: 19px; height: 19px; stroke-width: 2; }
.sb-toggle-btn svg { width: 14px; height: 14px; stroke-width: 2.2; }
.sb-mobile-launcher svg { width: 22px; height: 22px; stroke-width: 2.2; }
.sb-footer-btn svg { width: 16px; height: 16px; stroke-width: 2.2; }

/* ── لون الأيقونات حسب القسم ── */
.icon-cyan    { background: rgba(6,182,212,0.15);   color: #22d3ee; }
.icon-green   { background: rgba(16,185,129,0.15);  color: #34d399; }
.icon-emerald { background: rgba(52,211,153,0.15);  color: #6ee7b7; }
.icon-amber   { background: rgba(245,158,11,0.15);  color: #fbbf24; }
.icon-purple  { background: rgba(139,92,246,0.15);  color: #c084fc; }
.icon-indigo  { background: rgba(99,102,241,0.15);  color: #a5b4fc; }
.icon-red     { background: rgba(239,68,68,0.15);   color: #f87171; }
.icon-rose    { background: rgba(244,63,94,0.15);   color: #fb7185; }
.icon-teal    { background: rgba(20,184,166,0.15);  color: #5eead4; }
.icon-lime    { background: rgba(132,204,22,0.15);  color: #bef264; }
.icon-blue    { background: rgba(59,130,246,0.15);  color: #93c5fd; }
.icon-whatsapp{ background: rgba(34,197,94,0.15);   color: #86efac; }
.icon-gold    { background: rgba(96,165,250,0.18); color: #60a5fa; }
.icon-dark    { background: rgba(148,163,184,0.1);  color: #94a3b8; }

/* ── نص الرابط ── */
.sb-nav-label { flex: 1; line-height: 1.3; }
.sb-nav-label small {
    display: block;
    font-size: 0.72rem;
    font-weight: 500;
    color: #475569;
    margin-top: 1px;
    transition: color var(--sb-transition);
}
.sb-nav-link:hover .sb-nav-label small { color: #64748b; }

/* ── Badge الإشعارات ── */
.sb-badge {
    background: #ef4444;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 900;
    padding: 2px 6px;
    border-radius: 20px;
    line-height: 1.4;
    flex-shrink: 0;
}

/* ── قسم المدير ── */
.sb-admin-section .sb-section-title { color: #94a3b8; }
.sb-admin-section .sb-section-title i { color: var(--sb-accent); }
.sb-admin-section .sb-nav-link:hover { background: rgba(96,165,250,0.08); border-left-color: var(--sb-accent); }

/* ── ذيل السايد بار (الأزرار السريعة) ── */
.sb-footer {
    padding: 12px;
    border-top: 1px solid var(--sb-border);
    display: flex;
    flex-direction: column;
    gap: 7px;
    flex-shrink: 0;
    background: rgba(255,255,255,0.01);
}
.sb-footer-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: var(--sb-transition);
    color: #fff;
    width: 100%;
}
.sb-footer-btn:hover { filter: brightness(1.15); transform: translateY(-1px); color: #fff; }
.sb-btn-shift    { background: linear-gradient(135deg, #b45309, #f59e0b); color: #000 !important; }
.sb-btn-shift:hover { color: #000 !important; }
.sb-btn-telegram { background: linear-gradient(135deg, #0369a1, #0ea5e9); }
.sb-btn-logout   { background: linear-gradient(135deg, #991b1b, #ef4444); }

/* ── وضع الطي (Collapsed) ── */
.app-sidebar.collapsed {
    width: 64px;
}
.app-sidebar.collapsed ~ .main-content {
    margin-right: 64px !important;
    width: calc(100% - 64px) !important;
}
.app-sidebar.collapsed .sb-brand-text,
.app-sidebar.collapsed .sb-user-name,
.app-sidebar.collapsed .sb-user-role,
.app-sidebar.collapsed .sb-section-title,
.app-sidebar.collapsed .sb-nav-label,
.app-sidebar.collapsed .sb-badge,
.app-sidebar.collapsed .sb-footer-btn span {
    display: none;
}
.app-sidebar.collapsed .sb-nav-link {
    padding: 9px 0;
    justify-content: center;
    border-left: none;
}
.app-sidebar.collapsed .sb-nav-icon { margin: 0 auto; }
.app-sidebar.collapsed .sb-header { justify-content: center; padding: 20px 12px 15px; }
.app-sidebar.collapsed .sb-brand { display: none; }
.app-sidebar.collapsed .sb-toggle-btn { margin: 0 auto; }
.app-sidebar.collapsed .sb-user-info { padding: 14px 0; justify-content: center; }
.app-sidebar.collapsed .sb-user-badge { justify-content: center; }
.app-sidebar.collapsed .sb-footer-btn { padding: 9px 0; }
.app-sidebar.collapsed .sb-body { padding: 8px 0; }

/* ── تلميح Tooltip في وضع الطي ── */
.app-sidebar.collapsed .sb-nav-link {
    position: relative;
}
.app-sidebar.collapsed .sb-nav-link::before {
    content: attr(data-label);
    position: absolute;
    right: calc(100% + 8px);
    top: 50%;
    transform: translateY(-50%);
    background: #1e293b;
    color: #f1f5f9;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 6px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    border: 1px solid rgba(255,255,255,0.1);
    z-index: 2000;
}
.app-sidebar.collapsed .sb-nav-link:hover::before { opacity: 1; }

/* ── Responsive ── */
@media (max-width: 991px) {
    .app-sidebar {
        transform: translateX(100%);
        box-shadow: none;
    }
    .app-sidebar.mobile-open {
        transform: translateX(0);
        box-shadow: -8px 0 40px rgba(0,0,0,0.6);
    }
    /* ── على الموبايل: نلغي وضع الطي (collapsed) القادم من الديسكتوب ── */
    /* بدونها بيظهر شريط الأيقونات المضغوط ويزيح المحتوى ويخرب الشكل */
    .app-sidebar.collapsed {
        width: var(--sb-width);
        transform: translateX(100%);
    }
    .app-sidebar.collapsed.mobile-open { transform: translateX(0); }
    .app-sidebar.collapsed .sb-brand,
    .app-sidebar.collapsed .sb-user-badge { display: flex; }
    .app-sidebar.collapsed .sb-brand-text,
    .app-sidebar.collapsed .sb-user-name,
    .app-sidebar.collapsed .sb-user-role,
    .app-sidebar.collapsed .sb-section-title,
    .app-sidebar.collapsed .sb-nav-label,
    .app-sidebar.collapsed .sb-badge,
    .app-sidebar.collapsed .sb-footer-btn span { display: block; }
    .app-sidebar.collapsed .sb-nav-link {
        padding: 6px 16px;
        justify-content: flex-start;
        border-left: 3px solid transparent;
    }
    .app-sidebar.collapsed .sb-nav-icon { margin: 0; }
    .app-sidebar.collapsed .sb-nav-link::before { content: none; }
    .main-content,
    .app-sidebar.collapsed ~ .main-content {
        margin-right: 0 !important;
        width: 100% !important;
        padding: 70px 20px 40px !important;
    }
    .sb-mobile-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        z-index: 1040;
        backdrop-filter: blur(3px);
    }
    .sb-mobile-backdrop.show { display: block; }
    .sb-mobile-launcher {
        display: flex !important;
    }
}
@media (min-width: 992px) {
    .sb-mobile-launcher { display: none !important; }
    .sb-mobile-backdrop { display: none !important; }
}

/* ── زر فتح السايد بار على الجوال ── */
.sb-mobile-launcher {
    display: none;
    position: fixed;
    top: 15px;
    right: 15px;
    z-index: 1030;
    background: rgba(11, 21, 37, 0.9);
    backdrop-filter: blur(10px);
    border: 1px solid var(--sb-border);
    color: #fff;
    width: 46px;
    height: 46px;
    border-radius: 12px;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    transition: var(--sb-transition);
}
.sb-mobile-launcher:hover { background: var(--sb-gold); color: #0f172a; }

/* ── زر إغلاق السايدبار داخله (موبايل فقط) ── */
.sb-close-mobile {
    display: none;
    background: rgba(255,255,255,0.08);
    border: 1px solid var(--sb-border);
    color: #cbd5e1;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    font-size: 1rem;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    transition: var(--sb-transition);
    flex-shrink: 0;
}
.sb-close-mobile:hover { background: rgba(220,38,38,0.3); color: #fff; border-color: #dc2626; }
@media (max-width: 991px) {
    .sb-close-mobile { display: flex; }
    .sb-toggle-btn { display: none !important; }
}

/* ══════════════════════════════════════════════
   GLOBAL MOBILE RESPONSIVE — يشمل كل الشاشات
   ══════════════════════════════════════════════ */
@media (max-width: 991px) {
    /* ── عناصر رأس الصفحة ── */
    .main-content h2, .main-content h3 { font-size: 1.2rem !important; }
    .main-content h5, .main-content h6 { font-size: 0.95rem !important; }

    /* ── الجداول: تمرير أفقي لكل الجداول ── */
    table { min-width: 600px; }
    .table-responsive, div[style*="overflow"] { overflow-x: auto !important; -webkit-overflow-scrolling: touch; }

    /* ── الكارتات والإحصائيات: عمودين بدل أربعة ── */
    .row.g-3 > [class*="col-lg-3"],
    .row.g-3 > [class*="col-md-3"] { flex: 0 0 50%; max-width: 50%; }

    .row.g-3 > [class*="col-lg-4"],
    .row.g-3 > [class*="col-md-4"] { flex: 0 0 50%; max-width: 50%; }

    .row.g-3 > [class*="col-lg-6"],
    .row.g-3 > [class*="col-md-6"] { flex: 0 0 100%; max-width: 100%; }

    /* ── فلاتر وأزرار: ترتيب عمودي ── */
    .d-flex.gap-2.flex-wrap, .d-flex.gap-3.flex-wrap { gap: 6px !important; }
    .btn-group { flex-wrap: wrap; }

    /* ── المودالات: تمتد للشاشة كاملة ── */
    .modal-dialog { margin: 8px !important; max-width: calc(100vw - 16px) !important; }
    .modal-dialog.modal-xl, .modal-dialog.modal-lg { max-width: calc(100vw - 16px) !important; }
    .modal-body { padding: 12px !important; }

    /* ── Nav Tabs: تمرير أفقي ── */
    .nav-pills, .nav-tabs { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; padding-bottom: 4px; }
    .nav-pills .nav-item, .nav-tabs .nav-item { flex-shrink: 0; }

    /* ── Forms: إدخالات كاملة العرض ── */
    .input-group { flex-wrap: wrap; }
    .form-select[style*="width"], input[style*="width:"] { width: 100% !important; max-width: 100% !important; }

    /* ── إخفاء عناصر ثانوية ── */
    .d-none-mobile { display: none !important; }

    /* ── stat cards كومبكت على الموبايل ── */
    .stat-card { padding: 12px 14px !important; }
    .stat-card h3, .stat-card .value { font-size: 1.3rem !important; }
    .stat-card p { font-size: 0.68rem !important; }
}

@media (max-width: 576px) {
    /* ── الشاشات الصغيرة جداً: عمود واحد ── */
    .main-content { padding: 65px 12px 30px !important; }

    .row.g-3 > [class*="col-lg-3"],
    .row.g-3 > [class*="col-md-3"],
    .row.g-3 > [class*="col-sm-6"] { flex: 0 0 100%; max-width: 100%; }

    .row.g-3 > [class*="col-lg-4"],
    .row.g-3 > [class*="col-md-4"] { flex: 0 0 100%; max-width: 100%; }

    /* ── أزرار الإجراءات: حجم أصغر ── */
    .btn { font-size: 0.82rem !important; padding: 6px 10px !important; }
    .btn.rounded-pill { border-radius: 8px !important; }

    /* ── الجداول: نص أصغر ── */
    td, th { font-size: 0.78rem !important; padding: 6px 8px !important; }

    /* ── بادج الحالة ── */
    .badge { font-size: 0.7rem !important; }
}
</style>

{{-- زر فتح السايد بار على الجوال --}}
<div class="sb-mobile-launcher" id="sbMobileLauncher">
    {!! $icons['menu'] !!}
</div>

{{-- خلفية معتمة للجوال --}}
<div class="sb-mobile-backdrop" id="sbMobileBackdrop"></div>

{{-- ═══════════════════════════════════════════
     السايد بار الرئيسي
     ═══════════════════════════════════════════ --}}
<aside class="app-sidebar" id="appSidebar">

    {{-- رأس السايد بار --}}
    <div class="sb-header">
        <a href="{{ url('/') }}" class="sb-brand" title="الرئيسية">
            <div class="sb-brand-icon">{!! $icons['building'] !!}</div>
            <div class="sb-brand-text">
                <h6>شركة <span>الضبع</span></h6>
                <span>نظام إدارة الموارد</span>
            </div>
        </a>
        <button class="sb-toggle-btn" id="sbToggleBtn" title="طي القائمة / إغلاق">
            <span id="sbToggleIcon">{!! $icons['chev-left'] !!}</span>
        </button>
        {{-- زر الإغلاق على الموبايل فقط --}}
        <button type="button" class="sb-close-mobile" id="sbCloseMobile" title="إغلاق القائمة" onclick="if(window.closeMobileSidebar)window.closeMobileSidebar()">
            ✕
        </button>
    </div>

    {{-- معلومات المستخدم --}}
    <div class="sb-user-info">
        <div class="sb-user-badge">
            <div class="sb-user-avatar">{!! $icons['user'] !!}</div>
            <div>
                <div class="sb-user-name">{{ session('auth_user')?->name ?? 'موظف' }}</div>
                <div class="sb-user-role">{{ $isAdmin ? 'مدير النظام' : 'موظف' }}</div>
            </div>
        </div>
    </div>

    {{-- محتوى التنقل --}}
    <div class="sb-body">

        {{-- ═══ الماليات (في الأعلى) ═══ --}}
        <a href="{{ url('/financial-ops') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'financial') ? 'active' : '' }}" data-label="العمليات المالية">
            <div class="sb-nav-icon icon-indigo">{!! $icons['file'] !!}</div>
            <div class="sb-nav-label">العمليات المالية <small>تحويلات، عهد، إيداعات</small></div>
        </a>
        <a href="{{ url('/treasury') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'treasury') ? 'active' : '' }}" data-label="المركز المالي">
            <div class="sb-nav-icon icon-purple">{!! $icons['wallet'] !!}</div>
            <div class="sb-nav-label">المركز المالي (الخزائن) <small>أرصدة محافظ ورأس المال</small></div>
        </a>
        <a href="{{ url('/expenses') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'expenses') ? 'active' : '' }}" data-label="المصروفات">
            <div class="sb-nav-icon icon-red">{!! $icons['receipt'] !!}</div>
            <div class="sb-nav-label">دفتر المصروفات <small>إيجار، كهرباء، صيانة</small></div>
        </a>

        <div class="sb-group-sep"></div>

        {{-- ═══ السجلات (في الأعلى) ═══ --}}
        <a href="{{ url('/notifications') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'notifications') ? 'active' : '' }}" data-label="الإشعارات وسجل النشاط">
            <div class="sb-nav-icon icon-dark" style="position: relative;">
                {!! $icons['bell'] !!}
                @if($unreadCount > 0)
                    <span style="position:absolute;top:-4px;right:-4px;width:14px;height:14px;background:#ef4444;border-radius:50%;font-size:0.5rem;display:flex;align-items:center;justify-content:center;font-weight:900;border:2px solid var(--sb-bg);color:#fff;">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </div>
            <div class="sb-nav-label">الإشعارات وسجل النشاط <small>مراقبة حركات النظام</small></div>
            @if($unreadCount > 0)
                <span class="sb-badge">{{ $unreadCount }}</span>
            @endif
        </a>
        @if($isAdmin)
        <a href="{{ url('/operations-log') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'operations') ? 'active' : '' }}" data-label="سجل العمليات">
            <div class="sb-nav-icon icon-rose">{!! $icons['shield'] !!}</div>
            <div class="sb-nav-label">سجل العمليات <small>تعديل الإدخالات الخاطئة</small></div>
        </a>
        <a href="{{ url('/audit-log') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'audit') ? 'active' : '' }}" data-label="سجل التدقيق">
            <div class="sb-nav-icon icon-rose">{!! $icons['shield'] !!}</div>
            <div class="sb-nav-label">سجل التدقيق <small>كل تغيير في النظام</small></div>
        </a>
        @endif

        <div class="sb-group-sep"></div>

        {{-- ═══ باقي الصفحات ═══ --}}
        <a href="{{ url('/dashboard') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'dashboard') ? 'active' : '' }}" data-label="لوحة التحكم">
            <div class="sb-nav-icon icon-gold">{!! $icons['gauge'] !!}</div>
            <div class="sb-nav-label">لوحة التحكم <small>ملخص كل النظام</small></div>
        </a>
        <a href="{{ url('/reports') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'reports') ? 'active' : '' }}" data-label="التقارير والأرباح">
            <div class="sb-nav-icon icon-cyan">{!! $icons['bar'] !!}</div>
            <div class="sb-nav-label">التقارير والأرباح <small>تدفقات نقدية، أداء المبيعات</small></div>
        </a>
        <a href="{{ url('/sales') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'sales') ? 'active' : '' }}" data-label="بيع الخدمات">
            <div class="sb-nav-icon icon-emerald">{!! $icons['card'] !!}</div>
            <div class="sb-nav-label">بيع الخدمات <small>صيانة، تركيب، خدمات الفنيين</small></div>
        </a>
        <a href="{{ url('/inventory') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'inventory') ? 'active' : '' }}" data-label="إدارة المخزن">
            <div class="sb-nav-icon icon-green">{!! $icons['box'] !!}</div>
            <div class="sb-nav-label">إدارة المخزن <small>جرد، توريد، مرتجعات</small></div>
        </a>
        <a href="{{ url('/gas-station') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'gas') ? 'active' : '' }}" data-label="محطة الوقود">
            <div class="sb-nav-icon icon-amber">{!! $icons['fuel'] !!}</div>
            <div class="sb-nav-label">محطة الوقود <small>مسحوبات سولار واستقطاعات</small></div>
        </a>
        <a href="{{ route('goals.index') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'goals') ? 'active' : '' }}" data-label="الأهداف">
            <div class="sb-nav-icon icon-blue">{!! $icons['target'] !!}</div>
            <div class="sb-nav-label">الأهداف <small>تتبع الأهداف المالية</small></div>
        </a>
        <a href="{{ url('/installments') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'installments') ? 'active' : '' }}" data-label="منظومة الأقساط">
            <div class="sb-nav-icon icon-teal">{!! $icons['calendar'] !!}</div>
            <div class="sb-nav-label">منظومة الأقساط <small>عقود، فوائد، تحصيل</small></div>
        </a>
        <a href="{{ url('/debts') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'debts') && !str_ends_with($currentRoute, '2') ? 'active' : '' }}" data-label="مستحقات العملاء">
            <div class="sb-nav-icon icon-lime">{!! $icons['trend-up'] !!}</div>
            <div class="sb-nav-label">مستحقات العملاء (لنا) <small>ديون متأخرة، خصومات</small></div>
        </a>
        <a href="{{ url('/debts2') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'debts2') ? 'active' : '' }}" data-label="ديون الشركة">
            <div class="sb-nav-icon icon-rose">{!! $icons['trend-down'] !!}</div>
            <div class="sb-nav-label">ديون الشركة (علينا) <small>فواتير موردين، عمولات</small></div>
        </a>
        <a href="{{ url('/customers-archive') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'customers') ? 'active' : '' }}" data-label="أرشيف العملاء">
            <div class="sb-nav-icon icon-blue">{!! $icons['contacts'] !!}</div>
            <div class="sb-nav-label">أرشيف العملاء <small>قاعدة بيانات، ائتمان</small></div>
        </a>
        @php
            $pendingInquiries = \Illuminate\Support\Facades\DB::table('customer_inquiries')->where('is_contacted', 0)->count();
        @endphp
        <a href="{{ url('/inquiries') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'inquiries') ? 'active' : '' }}" data-label="استفسارات العملاء">
            <div class="sb-nav-icon icon-teal">{!! $icons['phone'] !!}</div>
            <div class="sb-nav-label">استفسارات العملاء <small>مكالمات وطلبات للمتابعة</small></div>
            @if($pendingInquiries > 0)
                <span class="sb-badge">{{ $pendingInquiries }}</span>
            @endif
        </a>
        <a href="{{ url('/assets') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'assets') ? 'active' : '' }}" data-label="الأصول الثابتة">
            <div class="sb-nav-icon icon-purple">{!! $icons['car'] !!}</div>
            <div class="sb-nav-label">الأصول الثابتة <small>سيارات، معدات، إهلاك</small></div>
        </a>
        <a href="{{ url('/whatsapp-center') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'whatsapp') ? 'active' : '' }}" data-label="مركز واتساب">
            <div class="sb-nav-icon icon-whatsapp">{!! $icons['message'] !!}</div>
            <div class="sb-nav-label">مركز واتساب <small>رسائل تذكير وكشوفات</small></div>
        </a>

        {{-- الإدارة العليا (للمدير فقط) --}}
        @if($isAdmin)
        <div class="sb-group-sep"></div>
        <div class="sb-admin-section">
            <a href="{{ url('/hr') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'hr') ? 'active' : '' }}" data-label="شؤون الموظفين">
                <div class="sb-nav-icon icon-cyan">{!! $icons['briefcase'] !!}</div>
                <div class="sb-nav-label">شؤون الموظفين (HR) <small>رواتب، سلف، تقفيل الشهر</small></div>
            </a>
            <a href="{{ url('settings') }}" class="sb-nav-link {{ str_starts_with($currentRoute, 'settings') ? 'active' : '' }}" data-label="الإعدادات العامة">
                <div class="sb-nav-icon icon-dark">{!! $icons['settings'] !!}</div>
                <div class="sb-nav-label">الإعدادات العامة <small>صلاحيات، موردين، خصومات</small></div>
            </a>
        </div>
        @endif

    </div>{{-- / sb-body --}}

    {{-- 🛡️ Global form handler (يطبّق على كل الصفحات) --}}
    @include('partials.global_form_handler')

    {{-- ذيل السايد بار --}}
    <div class="sb-footer">

        @if($isAdmin)
        {{-- زر تصفير قاعدة البيانات (للتيست فقط) --}}
        <form action="{{ route('dev.reset') }}" method="POST" id="resetDbForm" class="m-0">
            @csrf
        </form>
        <button type="button" onclick="confirmResetDB()"
                class="sb-footer-btn"
                style="background: linear-gradient(135deg, #1a0a00, #7c2d12); border: 1px dashed #f97316; font-size: 0.78rem;">
            <i class="fa fa-triangle-exclamation" style="color:#fb923c;"></i>
            <span style="color:#fed7aa;">تصفير قاعدة البيانات</span>
        </button>
        @endif

        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="sb-footer-btn sb-btn-logout">
                {!! $icons['logout'] !!}<span>تسجيل خروج</span>
            </button>
        </form>
    </div>

</aside>{{-- / app-sidebar --}}

{{-- ═══════════════════════════
     Modal تقفيل الوردية
     ═══════════════════════════ --}}
<div class="modal fade" id="closeShiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #0d1f35, #1a3a5f); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa fa-cash-register me-2 text-warning"></i>تقفيل الوردية وتسليم العهدة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('shift.close') }}" method="POST">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="alert alert-warning fw-bold small mb-4" style="background-color: #fffbeb; color: #b45309; border-color: #fde68a;">
                        <i class="fa fa-info-circle me-1"></i> قم بعد النقدية الموجودة في الدرج الفعلي وأدخل الإجمالي هنا. النظام سيراجع المبلغ ويسجل أي عجز أو زيادة فوراً.
                    </div>
                    @php $safes = \Illuminate\Support\Facades\DB::table('accounts')->where('category', 'safe_cash')->get(); @endphp
                    <div class="mb-3 text-start">
                        <label class="fw-bold small text-muted mb-1">الخزنة / الدرج</label>
                        <select name="account_id" class="form-select fw-bold border-dark" required>
                            <option value="" disabled selected>اختر الدرج الذي تعمل عليه...</option>
                            @foreach($safes as $safe) <option value="{{ $safe->id }}">{{ $safe->account_name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="mb-4 text-start">
                        <label class="fw-bold small text-primary mb-1">إجمالي الكاش الفعلي في يدك (ج)</label>
                        <input type="number" step="0.01" name="actual_amount" class="form-control form-control-lg border-primary fw-bold text-center fs-3 text-primary" placeholder="مثال: 5400" required>
                    </div>
                    <div class="mb-2 text-start">
                        <label class="fw-bold small text-muted mb-1">ملاحظات (اختياري)</label>
                        <input type="text" name="notes" class="form-control fw-bold" placeholder="سبب العجز إن وجد...">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0 bg-light" style="border-radius: 0 0 20px 20px;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn rounded-pill px-4 fw-bold text-white w-50" style="background: #1a3a5f;"><i class="fa fa-lock me-2"></i>اعتماد التقفيل</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const sidebar   = document.getElementById("appSidebar");
    const toggleBtn = document.getElementById("sbToggleBtn");
    const toggleIcon = document.getElementById("sbToggleIcon");
    const launcher  = document.getElementById("sbMobileLauncher");
    const backdrop  = document.getElementById("sbMobileBackdrop");

    // ── حالة الطي من localStorage ──
    const isMobile = () => window.innerWidth < 992;
    const svgLeft  = `{!! $icons['chev-left'] !!}`;
    const svgRight = `{!! $icons['chev-right'] !!}`;

    const savedCollapsed = localStorage.getItem("sb_collapsed") === "true";
    if (!isMobile() && savedCollapsed) {
        sidebar.classList.add("collapsed");
        if (toggleIcon) toggleIcon.innerHTML = svgRight;
    }

    // ── مزامنة حالة الطي مع حجم الشاشة ──
    // على الموبايل لازم نشيل "collapsed" عشان مايظهرش شريط الأيقونات المضغوط
    // ويرجع المحتوى لكامل العرض. وعند الرجوع للديسكتوب نطبّق الحالة المحفوظة.
    function syncCollapsedWithViewport() {
        if (isMobile()) {
            sidebar.classList.remove("collapsed");
        } else if (localStorage.getItem("sb_collapsed") === "true") {
            sidebar.classList.add("collapsed");
            if (toggleIcon) toggleIcon.innerHTML = svgRight;
        }
    }
    syncCollapsedWithViewport();
    let _sbResizeTimer;
    window.addEventListener("resize", function () {
        clearTimeout(_sbResizeTimer);
        _sbResizeTimer = setTimeout(syncCollapsedWithViewport, 150);
    });

    // ── زر الطي (سطح المكتب) / إغلاق (جوال) ──
    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            if (isMobile()) {
                closeMobileSidebar();   // على الموبايل: إغلاق السايدبار
                return;
            }
            sidebar.classList.toggle("collapsed");
            const collapsed = sidebar.classList.contains("collapsed");
            localStorage.setItem("sb_collapsed", collapsed);
            if (toggleIcon) toggleIcon.innerHTML = collapsed ? svgRight : svgLeft;
        });
    }

    // ── فتح/إغلاق السايد بار على الجوال (دوال عامة) ──
    window.openMobileSidebar = function () {
        if (!sidebar) return;
        sidebar.classList.add("mobile-open");
        if (backdrop) backdrop.classList.add("show");
        document.body.style.overflow = "hidden";  // منع التمرير خلف السايدبار
    };
    window.closeMobileSidebar = function () {
        if (!sidebar) return;
        sidebar.classList.remove("mobile-open");
        if (backdrop) backdrop.classList.remove("show");
        document.body.style.overflow = "";
    };
    const openMobileSidebar  = window.openMobileSidebar;
    const closeMobileSidebar = window.closeMobileSidebar;

    if (launcher) launcher.addEventListener("click", openMobileSidebar);
    if (backdrop) backdrop.addEventListener("click", closeMobileSidebar);

    // ── زر الإغلاق (✕) داخل السايدبار ──
    const closeBtn = document.getElementById("sbCloseMobile");
    if (closeBtn) closeBtn.addEventListener("click", closeMobileSidebar);

    // ── إغلاق السايدبار لما تضغط على رابط (موبايل) ──
    sidebar.querySelectorAll(".sb-nav-link").forEach(function(link) {
        link.addEventListener("click", function() {
            if (isMobile()) closeMobileSidebar();
        });
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && sidebar.classList.contains("mobile-open")) {
            closeMobileSidebar();
        }
    });

    // ── تصفير قاعدة البيانات ──
    window.confirmResetDB = function() {
        var step1 = confirm('⚠️ تحذير خطير!\n\nسيتم حذف كل البيانات التشغيلية:\nمبيعات، بنزينة، أقساط، ديون، ماليات، مخزون...\n\nالأرصدة هتبقى صفر.\nالإعدادات والمستخدمين هيفضلوا.\n\nهل تريد المتابعة؟');
        if (!step1) return;
        var word = prompt('اكتب بالضبط  →  تصفير  ←  للتأكيد:');
        if (word === null) return;
        if (word.trim() !== 'تصفير') {
            alert('❌ الكلمة غلط — لم يتم التصفير.');
            return;
        }
        document.getElementById('resetDbForm').submit();
    };

    // ── إشعارات SweetAlert2 ──
    if (typeof Swal !== "undefined") {
        const Toast = Swal.mixin({
            toast: true, position: "top-start", showConfirmButton: false,
            timer: 4500, timerProgressBar: true
        });
        @if(session('success'))
            Toast.fire({ icon: "success", title: "عملية ناجحة", text: "{{ session('success') }}", background: "#f0fdf4", color: "#166534" });
        @endif
        @if(session('error'))
            Toast.fire({ icon: "error", title: "خطأ بالنظام", text: "{{ session('error') }}", background: "#fef2f2", color: "#991b1b" });
        @endif

        // ── تنبيه الرصيد المنخفض ──
        @php
            try {
                $lowThreshold = (float) \App\Services\SystemSetting::get('low_balance_threshold', 0);
                $lowAccounts = $lowThreshold > 0
                    ? \Illuminate\Support\Facades\DB::table('accounts')
                        ->whereIn('category', ['bank_wallet', 'safe_cash'])
                        ->where('balance', '<', $lowThreshold)
                        ->orderBy('balance')->get()
                    : collect();
            } catch (\Throwable $e) {
                $lowAccounts = collect();
                $lowThreshold = 0;
            }
            $alertKey = 'lowbal_' . md5($lowAccounts->pluck('id')->implode(',') . '|' . $lowThreshold);
            $lowAccountsData = $lowAccounts->map(function ($a) {
                return [
                    'name'    => $a->account_name,
                    'balance' => number_format($a->balance, 0),
                    'cat'     => $a->category === 'bank_wallet' ? 'بنك / محفظة' : 'خزنة نقدية',
                ];
            })->values();
        @endphp
        @if($lowAccounts->count() > 0)
            (function () {
                var seen = sessionStorage.getItem('{{ $alertKey }}');
                if (seen) return;
                sessionStorage.setItem('{{ $alertKey }}', '1');

                var accounts = @json($lowAccountsData);

                var rows = accounts.map(function (a) {
                    return '<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; background:#fff; border:1px solid #fde68a; margin-bottom:8px;">' +
                        '<div style="display:flex; align-items:center; gap:10px;">' +
                            '<span style="width:34px; height:34px; border-radius:9px; background:#fef3c7; display:inline-flex; align-items:center; justify-content:center; color:#b45309;"><i class="fa fa-wallet"></i></span>' +
                            '<div style="text-align:right;"><div style="font-weight:800; color:#1f2937;">' + a.name + '</div>' +
                            '<div style="font-size:11px; color:#9ca3af;">' + a.cat + '</div></div>' +
                        '</div>' +
                        '<div style="font-weight:900; color:#dc2626; white-space:nowrap; font-size:15px;">' + a.balance + ' ج</div>' +
                    '</div>';
                }).join('');

                Swal.fire({
                    position: 'top',
                    width: 460,
                    background: '#fffbeb',
                    showConfirmButton: true,
                    confirmButtonText: 'تمام، خدت بالي',
                    confirmButtonColor: '#d97706',
                    showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
                    html:
                        '<div style="text-align:center; margin-bottom:14px;">' +
                            '<div style="width:64px; height:64px; margin:0 auto 10px; border-radius:50%; background:#fef3c7; display:flex; align-items:center; justify-content:center; box-shadow:0 0 0 8px rgba(251,191,36,0.18);">' +
                                '<i class="fa fa-triangle-exclamation" style="font-size:28px; color:#d97706;"></i>' +
                            '</div>' +
                            '<h3 style="margin:0; font-weight:900; color:#92400e; font-size:20px;">تنبيه: رصيد منخفض</h3>' +
                            '<p style="margin:6px 0 0; color:#a16207; font-size:13px;">' + accounts.length + ' من الخزن رصيدها أقل من حد التنبيه (' + '{{ number_format($lowThreshold, 0) }}' + ' ج)</p>' +
                        '</div>' +
                        '<div style="max-height:300px; overflow-y:auto; padding:2px;">' + rows + '</div>',
                });
            })();
        @endif
    }

    /* ── تغليف الجداول الـ bare بـ overflow wrapper على الموبايل ── */
    if (window.innerWidth < 992) {
        document.querySelectorAll('table').forEach(function(tbl) {
            if (!tbl.closest('.table-responsive') && !tbl.closest('[style*="overflow"]')) {
                var wrap = document.createElement('div');
                wrap.style.overflowX = 'auto';
                wrap.style.webkitOverflowScrolling = 'touch';
                tbl.parentNode.insertBefore(wrap, tbl);
                wrap.appendChild(tbl);
            }
        });
    }
});
</script>