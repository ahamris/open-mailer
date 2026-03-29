<!DOCTYPE html>
<html lang="nl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CLOM') — CodeLabs Open Mailer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">
    <div class="drawer lg:drawer-open">
        <input id="sidebar" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content">
            <div class="navbar bg-base-100 shadow-sm lg:hidden">
                <div class="flex-none">
                    <label for="sidebar" class="btn btn-square btn-ghost drawer-button">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </label>
                </div>
                <div class="flex-1"><a class="btn btn-ghost text-xl" href="/admin">CLOM</a></div>
            </div>
            <main class="p-6">
                @if(session('success'))
                    <div class="alert alert-success mb-4 shadow-sm"><span>{{ session('success') }}</span></div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error mb-4 shadow-sm"><span>{{ session('error') }}</span></div>
                @endif
                @yield('content')
            </main>
        </div>
        <div class="drawer-side">
            <label for="sidebar" class="drawer-overlay"></label>
            <aside class="bg-base-100 w-64 min-h-screen border-r border-base-300 flex flex-col">
                <div class="p-4 border-b border-base-300">
                    <a href="/admin" class="text-2xl font-bold block">CLOM</a>
                    <p class="text-xs text-base-content/60">CodeLabs Open Mailer v0.1</p>
                </div>
                <ul class="menu p-4 gap-0.5 flex-1">
                    <li class="menu-title text-xs uppercase tracking-wider mt-2">Overzicht</li>
                    <li><a href="/admin" class="{{ request()->is('admin') && !request()->is('admin/*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a></li>

                    <li class="menu-title text-xs uppercase tracking-wider mt-4">Mail Client</li>
                    <li><a href="/admin/mail" class="{{ request()->is('admin/mail*') && !request()->is('admin/mail/compose') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        Inbox
                        @php $unread = \App\Models\Email::where('direction','inbound')->where('is_read',false)->count(); @endphp
                        @if($unread > 0)<span class="badge badge-primary badge-xs">{{ $unread }}</span>@endif
                    </a></li>
                    <li><a href="/admin/mail/compose" class="{{ request()->is('admin/mail/compose') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Opstellen
                    </a></li>

                    <li class="menu-title text-xs uppercase tracking-wider mt-4">Automatisering</li>
                    <li><a href="/admin/workflows" class="{{ request()->is('admin/workflows*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Workflows
                    </a></li>
                    <li class="menu-title text-xs uppercase tracking-wider mt-4">AI</li>
                    <li><a href="/admin/ai-settings" class="{{ request()->is('admin/ai-settings*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        AI Instellingen
                    </a></li>

                    <li class="menu-title text-xs uppercase tracking-wider mt-4">Beheer</li>
                    <li><a href="/admin/emails" class="{{ request()->is('admin/emails*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Email Logs
                    </a></li>
                    <li><a href="/admin/domains" class="{{ request()->is('admin/domains*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                        Domeinen
                    </a></li>
                    <li><a href="/admin/api-keys" class="{{ request()->is('admin/api-keys*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        API Keys
                    </a></li>

                    <li class="menu-title text-xs uppercase tracking-wider mt-4">Documentatie</li>
                    <li><a href="/admin/docs/guide" class="{{ request()->is('admin/docs/guide') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Admin Guide
                    </a></li>
                    <li><a href="/admin/docs/api" class="{{ request()->is('admin/docs/api') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        API Docs
                    </a></li>
                </ul>
                <div class="p-4 border-t border-base-300">
                    <div class="flex items-center gap-2">
                        <div class="avatar placeholder"><div class="bg-primary text-primary-content rounded-full w-8"><span class="text-xs">CL</span></div></div>
                        <div class="text-sm"><p class="font-medium">{{ auth()->guard('admin')->user()?->name ?? 'Admin' }}</p></div>
                        <form method="POST" action="/admin/logout" class="ml-auto">@csrf<button class="btn btn-ghost btn-xs">Logout</button></form>
                    </div>
                </div>
            </aside>
        </div>
    </div>
    @yield('scripts')
</body>
</html>
