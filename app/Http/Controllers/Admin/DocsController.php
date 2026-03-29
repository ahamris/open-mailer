<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DocsController extends Controller
{
    public function api()     { return view('admin.docs.api'); }
    public function guide()   { return view('admin.docs.guide'); }
    public function swagger() { return view('admin.docs.swagger'); }
}
