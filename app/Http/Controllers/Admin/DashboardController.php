<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Email;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'sentToday' => Email::where('direction', 'outbound')->whereDate('created_at', today())->count(),
            'receivedToday' => Email::where('direction', 'inbound')->whereDate('created_at', today())->count(),
            'failedToday' => Email::whereIn('status', ['failed', 'bounced'])->whereDate('created_at', today())->count(),
            'activeDomains' => Domain::where('status', 'verified')->count(),
            'recentEmails' => Email::orderByDesc('created_at')->limit(10)->get(),
        ]);
    }
}
