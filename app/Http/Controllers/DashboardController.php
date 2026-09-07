<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard for authenticated users.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard');
    }
}
