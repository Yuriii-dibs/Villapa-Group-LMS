<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Redirect to the School Administrator dashboard.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }
}
