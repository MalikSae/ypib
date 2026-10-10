<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComponentShowcaseController extends Controller
{
    public function index()
    {
        // Halaman pengembangan: tidak tersedia di staging/produksi.
        abort_unless(app()->environment('local'), 404);

        return view('admin.komponen.index');
    }
}
