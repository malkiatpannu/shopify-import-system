<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use App\Models\Product;
use App\Models\Upload;
use App\Models\ImportRecord;

class DashboardController extends Controller
{
    public function index()
    {
        $uploads    =   Upload::latest()
                        ->paginate(10);

        $products   =   Product::with('upload')
                        ->latest()
                        ->paginate(20);

        $stats      =   [
            'uploads'   =>  Upload::count(),
            'products'  =>  Product::count(),
            'success'   =>  ImportRecord::where(
                                'status',
                                'success'
                            )->count(),
            'failed'    =>  ImportRecord::where(
                                'status',
                                'failed'
                            )->count(),
        ];

        $errors     =   ErrorLog::latest()
                        ->take(20)
                        ->get();

        return view(
            'dashboard.index',
            compact(
                'uploads',
                'products',
                'stats',
                'errors'
            )
        );
    }
}