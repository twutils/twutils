<?php

namespace App\Http\Controllers;

use App\Task;
use App\Export;
use Illuminate\Http\Request;

class ExportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request, Task $task, Export $export)
    {
        $this->authorize('view', [$export, $task]);

        return $export->toResponse();
    }
}
