<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function show(Upload $upload)
    {
        $imports = $upload->importRecords()
            ->latest()
            ->paginate(5);

        return view('imports.show', compact(
            'upload',
            'imports'
        ));
    }
}
