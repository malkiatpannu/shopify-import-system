<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        $logs = ErrorLog::latest()->paginate(20);

        return view('logs.index', compact('logs'));
    }
}
