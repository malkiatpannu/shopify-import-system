<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCsvImportJob;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function index()
    {
        return view('uploads.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file'      =>  [
                'required',
                'file',
                'mimes:csv,txt',
                'max:5120'
            ]
        ]);

        try {
            $file       =   $request->file('csv_file');
            $storedPath =   $file->store('imports', 'local');
            $upload     =   Upload::create([
                                'original_filename' =>  $file->getClientOriginalName(),
                                'stored_filename'   =>  basename($storedPath),
                                'file_path'         =>  $storedPath,
                                'status'            =>  'pending'
                            ]);
            Log::info('dispatching job');
            ProcessCsvImportJob::dispatch($upload->id);

            return redirect()
                ->route('dashboard')
                ->with(
                    'success',
                    'CSV uploaded successfully. Import process started.'
                );

        } catch (\Exception $e) {
            Log::error('Upload Failed', [
                'message' => $e->getMessage()
            ]);
            return back()
                ->withErrors([
                    'csv_file' => $e->getMessage()
                ]);
        }
    }
}