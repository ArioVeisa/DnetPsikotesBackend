<?php

namespace App\Http\Controllers;

class TestAccessController extends Controller
{
    // Halaman ini hanya untuk Super Admin & Admin HR
    public function manageTests()
    {
        return response()->json(['message' => 'Welcome to Test Management Page.']);
    }

    // Halaman ini untuk semua role (Super Admin, Admin HR, Viewer)
    public function viewReports()
    {
        return response()->json(['message' => 'Welcome to the Reports Page.']);
    }
}