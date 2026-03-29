<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CLOM') &middot; Open Mailer</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="sidenav">
        <div class="sidenav__brand">
            <a href="/admin" style="display:flex;align-items:center;gap:.625rem;text-decoration:none;">
                <div class="sidenav__brand-icon">C</div>
                <div>
                    <span class="sidenav__brand-text">CLOM</span>
                    <span class="sidenav__brand-sub">Open Mailer</span>
                </div>
            </a>
        </div>

        <ul class="sidenav__items">
            <li>
                <a href="/admin" class="sidenav__item-link {{ request()->is('admin') && !request()->is('admin/*') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Overview
                </a>
            </li>

            <li class="sidenav__section-title">Mail</li>
            <li>
                <a href="/admin/mail" class="sidenav__item-link {{ request()->is('admin/mail') && !request()->is('admin/mail/compose') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    Inbox
                    @php $unread = \App\Models\Email::where('direction','inbound')->where('is_read',false)->count(); @endphp
                    @if($unread > 0)<span class="sidenav__item-badge">{{ $unread }}</span>@endif
                </a>
            </li>
            <li>
                <a href="/admin/mail/compose" class="sidenav__item-link {{ request()->is('admin/mail/compose') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Compose
                </a>
            </li>

            <li class="sidenav__section-title">Campaigns</li>
            <li>
                <a href="/admin/templates" class="sidenav__item-link {{ request()->is('admin/templates*') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Templates
                </a>
            </li>
            <li>
                <a href="/admin/contacts" class="sidenav__item-link {{ request()->is('admin/contacts*') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Contacts
                </a>
            </li>
            <li>
                <a href="/admin/broadcasts" class="sidenav__item-link {{ request()->is('admin/broadcasts*') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    Broadcasts
                </a>
            </li>

            <li class="sidenav__section-title">Automation</li>
            <li>
                <a href="/admin/workflows" class="sidenav__item-link {{ request()->is('admin/workflows*') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Workflows
                </a>
            </li>

            <li class="sidenav__section-title">Settings</li>
            <li>
                <a href="/admin/emails" class="sidenav__item-link {{ request()->is('admin/emails*') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Email Logs
                </a>
            </li>
            <li>
                <a href="/admin/domains" class="sidenav__item-link {{ request()->is('admin/domains*') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                    Domains
                </a>
            </li>
            <li>
                <a href="/admin/api-keys" class="sidenav__item-link {{ request()->is('admin/api-keys*') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    API Keys
                </a>
            </li>
            <li>
                <a href="/admin/ai-settings" class="sidenav__item-link {{ request()->is('admin/ai-settings*') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    AI Settings
                </a>
            </li>

            <li class="sidenav__section-title">Documentation</li>
            <li>
                <a href="/admin/docs/guide" class="sidenav__item-link {{ request()->is('admin/docs/guide') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Admin Guide
                </a>
            </li>
            <li>
                <a href="/admin/docs/api" class="sidenav__item-link {{ request()->is('admin/docs/api') ? 'sidenav__item-link--active' : '' }}">
                    <svg class="sidenav__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    API Reference
                </a>
            </li>
        </ul>

        <div class="sidenav__footer">
            <div class="sidenav__avatar">{{ substr(auth()->guard('admin')->user()?->name ?? 'A', 0, 1) }}</div>
            <span class="sidenav__user">{{ auth()->guard('admin')->user()?->name ?? 'Admin' }}</span>
            <form method="POST" action="/admin/logout">@csrf
                <button class="btn btn--ghost btn--sm" title="Sign out" style="padding:0 .25rem;">
                    <svg style="width:1rem;height:1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </nav>

    <div class="main-content">
        <header class="topbar">
            <div>
                <div class="topbar__title">@yield('title', 'Dashboard')</div>
                @hasSection('subtitle')<div class="topbar__subtitle">@yield('subtitle')</div>@endif
            </div>
            <div style="display:flex;align-items:center;gap:.75rem;">@yield('actions')</div>
        </header>

        <div class="main-content__inner">
            @if(session('success'))
                <div class="alert alert--success">
                    <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert--danger">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
    @yield('scripts')
</body>
</html>
