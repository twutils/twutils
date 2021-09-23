<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Export;
use Illuminate\Http\Request;

class ExportsController extends Controller
{
    public function show(Request $request, Task $task, Export $export)
    {
        $this->authorize('view', [$export, $task]);

        return $export->toResponse();
    }
}
