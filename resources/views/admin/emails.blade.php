@extends('layouts.admin')
@section('title', 'Email Logs')
@section('subtitle', 'All sent and received emails')

@section('actions')
<div class="flex gap-1 bg-gray-100 rounded-lg p-0.5">
    <a href="/admin/emails?direction=all" class="px-3 py-1.5 text-sm rounded-md {{ request('direction', 'all') === 'all' ? 'bg-white shadow-sm font-medium text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">All</a>
    <a href="/admin/emails?direction=outbound" class="px-3 py-1.5 text-sm rounded-md {{ request('direction') === 'outbound' ? 'bg-white shadow-sm font-medium text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">Outbound</a>
    <a href="/admin/emails?direction=inbound" class="px-3 py-1.5 text-sm rounded-md {{ request('direction') === 'inbound' ? 'bg-white shadow-sm font-medium text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">Inbound</a>
</div>
@endsection

@section('content')
<div class="card">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Status</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Dir</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">From</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">To</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Subject</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Auth</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($emails as $email)
            <tr class="border-b border-gray-50 hover:bg-gray-50 cursor-pointer" onclick="document.getElementById('modal-{{ $email->id }}').showModal()">
                <td class="px-5 py-3">
