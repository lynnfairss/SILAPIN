<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;

class WebsiteController extends Controller
{
    public function index()
    {
        $inventaris = Inventaris::with('kategori', 'fotos')->get();

        return view('website.index', compact('inventaris'));
    }
}