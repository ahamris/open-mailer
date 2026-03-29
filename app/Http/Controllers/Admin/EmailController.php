<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Email;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index(Request $request)
    {
        $query = Email::orderByDesc('created_at');

        if ($request->direction && $request->direction !== 'all') {
            $query->where('direction', $request->direction);
        }

        return view('admin.emails', [
            'emails' => $query->paginate(25),
        ]);
    }
}
