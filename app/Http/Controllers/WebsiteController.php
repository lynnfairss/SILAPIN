<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\Kategori;

class WebsiteController extends Controller
{
    public function index()
    {
        $inventaris = Inventaris::with('kategori', 'fotos')->get();
        $kategori = Kategori::all();

        return view('website.index', compact('inventaris', 'kategori'));
    }
}