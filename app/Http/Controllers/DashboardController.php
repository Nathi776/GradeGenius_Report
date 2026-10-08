<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        return match (Auth::user()->role) {
            'parent' => redirect()->route('parent.reports'),
            'teacher' => redirect()->route('teacher.reports'),
            'administrator' => redirect()->route('administrator.reports'),
            default => redirect()->route('student.report'),
        };
    }
}
