<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use Illuminate\View\View;

class FileUploadController extends Controller
{
    public function index(): View
    {
        $files = FileUpload::orderByDesc('created_at')->get();

        return view('files.index', ['files' => $files]);
    }
}
