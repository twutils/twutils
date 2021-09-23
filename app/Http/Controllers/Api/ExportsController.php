<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ExportsController extends Controller
{
    public function delete(Request $request, Export $export): JsonResponse
    {
        $this->authorize('delete', $export);

        $export->delete();

        return response()->json($export);
    }

    public function add(Request $request, Task $task, $exportType): JsonResponse
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

            return response()->json($lastFoundExport);
        }

        $export = Export::create([
            'task_id' => $task->id,
            'type'    => $exportType,
        ]);

        $export->status = Export::STATUS_STARTED;
        $export->save();

        return response()->json($export);
    }
}
