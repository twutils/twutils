<?php

namespace App\Http\Controllers\Api;

use App\Task;
use App\Export;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class ExportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function delete(Request $request, Export $export)
    {
        $this->authorize('delete', $export);

        $export->delete();

        return $export;
    }

    public function add(Request $request, Task $task, $exportType)
    {
        $this->validate($request, [
            'type' => [
                'required',
                Rule::in(Export::AVAILABLE_TYPES),
            ],
        ]);

        $this->authorize('add', [Export::class, $task, $exportType]);

        $lastFoundExport = $task->exports()->where('type', $exportType)->get()->last();

        if (
            $lastFoundExport &&
            in_array($lastFoundExport->status, [Export::STATUS_INITIAL])
        ) {
            $lastFoundExport->status = Export::STATUS_STARTED;
            $lastFoundExport->save();

            return $lastFoundExport;
        }

        $export = Export::create([
            'task_id' => $task->id,
            'type'    => $exportType,
        ]);

        $export->status = Export::STATUS_STARTED;
        $export->save();
    }
}
