<?php

namespace App\Http\Controllers;

use App\Task;
use App\Download;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DownloadsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request, Task $task, Download $download)
    {
    	$this->authorize('view', [$download, $task]);

    	return $download->toResponse();
    }
}
