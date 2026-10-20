<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $admin = $request->user('admin');

        if (! $admin->hasPermission('dashboard:view')) {
            return to_route($admin->homeRoute());
        }

        return view('admin.dashboard.index');
    }
}
