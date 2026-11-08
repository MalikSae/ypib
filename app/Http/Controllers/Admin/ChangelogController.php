<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/** Riwayat update per deploy. Isi dikelola di resources/data/changelog.php. */
class ChangelogController extends Controller
{
    public function index()
    {
        $releases = require resource_path('data/changelog.php');

        return view('admin.changelog.index', compact('releases'));
    }
}
