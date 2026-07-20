<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;

class WebsiteController extends Controller
{
    public function index()
    {
        $inventaris = Inventaris::with('kategori')->get();

        return view('website.index', compact('inventaris'));
    }
}