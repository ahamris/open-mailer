<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CLOM') — Open Mailer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --mb-green: #22c55e; --mb-blue: #3b82f6; --mb-sidebar: #f8fafc; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .sidebar-item { @apply flex items-center gap-3 px-4 py-2 text-sm text-gray-600 rounded-lg transition-colors hover:bg-gray-100; }
        .sidebar-item.active { @apply bg-blue-50 text-blue-600 font-medium; }
        .sidebar-section { @apply text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 mt-6 mb-2; }
        .card { @apply bg-white rounded-xl border border-gray-200; }
        .stat-card { @apply bg-white rounded-xl border border-gray-200 p-5; }
        .btn-green { @apply bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-4 py-2 rounded-lg text-sm transition-colors; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-56 bg-white border-r border-gray-200 min-h-screen fixed left-0 top-0 flex flex-col z-10">
            <div class="p-4 border-b border-gray-100">
                <a href="/admin" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">C</span>
                    </div>
                    <div>
                        <span class="font-bold text-gray-900">CLOM</span>
                        <span class="text-xs text-gray-400 block leading-none">Open Mailer</span>
                    </div>
                </a>
            </div>

            <nav class="flex-1 py-2 overflow-y-auto">
                <a href="/admin" class="sidebar-item {{ request()->is('admin') && !request()->is('admin/*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Overview
                </a>

                <div class="sidebar-section">Mail</div>
                <a href="/admin/mail" class="sidebar-item {{ request()->is('admin/mail') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    Inbox
                    @php $unread = \App\Models\Email::where('direction','inbound')->where('is_read',false)->count(); @endphp
                    @if($unread > 0)<span class="ml-auto bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $unread }}</span>@endif
                </a>
                <a href="/admin/mail/compose" class="sidebar-item {{ request()->is('admin/mail/compose') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Compose
                </a>

                <div class="sidebar-section">Automation</div>
                <a href="/admin/workflows" class="sidebar-item {{ request()->is('admin/workflows*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Workflows
                </a>

                <div class="sidebar-section">Settings</div>
                <a href="/admin/emails" class="sidebar-item {{ request()->is('admin/emails*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Email Logs
                </a>
                <a href="/admin/domains" class="sidebar-item {{ request()->is('admin/domains*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                    Domains
                </a>
                <a href="/admin/api-keys" class="sidebar-item {{ request()->is('admin/api-keys*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    API Keys
                </a>
                <a href="/admin/ai-settings" class="sidebar-item {{ request()->is('admin/ai-settings*') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    AI Settings
                </a>

                <div class="sidebar-section">Documentation</div>
                <a href="/admin/docs/guide" class="sidebar-item {{ request()->is('admin/docs/guide') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Admin Guide
                </a>
                <a href="/admin/docs/api" class="sidebar-item {{ request()->is('admin/docs/api') ? 'active' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    API Reference
                </a>
            </nav>

            <div class="p-4 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">
                        {{ substr(auth()->guard('admin')->user()?->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->guard('admin')->user()?->name ?? 'Admin' }}</p>
                    </div>
                    <form method="POST" action="/admin/logout">@csrf
                        <button class="text-gray-400 hover:text-gray-600" title="Sign out">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 ml-56">
            <!-- Top bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-10">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                    @hasSection('subtitle')
                        <p class="text-sm text-gray-500 mt-0.5">@yield('subtitle')</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @yield('actions')
                </div>
            </header>

            <div class="px-8 py-6 max-w-7xl">
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    @yield('scripts')
</body>
</html>
