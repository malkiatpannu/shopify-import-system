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
            if (!$file->isValid()) {
                throw new Exception('Uploaded file is invalid or corrupted.');
            }
            $storedPath =   $file->store('imports', 'local');
            if (!$storedPath) {
                throw new Exception('Failed to store uploaded file.');
            }
            $upload     =   Upload::create([
                                'original_filename' =>  $file->getClientOriginalName(),
                                'stored_filename'   =>  basename($storedPath),
                                'file_path'         =>  $storedPath,
                                'status'            =>  'pending'
                            ]);
            try {
                ProcessCsvImportJob::dispatch($upload->id);
                Log::info('CSV import job dispatched successfully.', [
                    'upload_id' => $upload->id,
                    'file'      => $upload->original_filename,
                ]);
            }catch (Exception $e) {

                $upload->update([
                    'status' => 'failed',
                ]);

                ErrorLog::create([
                    'upload_id' => $upload->id,
                    'source'    => 'Queue Dispatch',
                    'message'   => $e->getMessage(),
                ]);

                Log::error('Failed to dispatch CSV import job.', [
                    'upload_id' => $upload->id,
                    'error'     => $e->getMessage(),
                ]);

                throw $e;
            }


            return redirect()
                ->route('dashboard')
                ->with(
                    'success',
                    'CSV uploaded successfully. Import process has started.'
                );

        } catch (\Exception $e) {
            Log::error('CSV upload failed.', [
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->withErrors([
                    'csv_file' => 'Unable to upload CSV file. ' . $e->getMessage(),
                ]);;
        }
    }
}