<?php

namespace App\Http\Controllers\Api;

use App\Task;
use App\Export;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class ExportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function delete(Request $request, Export $export)
    {
        $this->authorize('delete', $export);
    }
}
