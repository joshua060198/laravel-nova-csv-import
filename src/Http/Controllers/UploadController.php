<?php

namespace SimonHamp\LaravelNovaCsvImport\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Laravel\Nova\Http\Requests\NovaRequest;
use SimonHamp\LaravelNovaCsvImport\Importer;

class UploadController
{
    use Importable;

    public function handle(NovaRequest $request)
    {
        $data = Validator::make($request->all(), [
            'file' => 'required|file',
        ])->validate();

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        try {
            (new Importer)->toCollection($file, null);
        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'message' => 'Sorry, we could not import that file'], 422);
        }

        // Store the file temporarily
        // $hash = File::hash($file->getRealPath()) . "." . $extension;

        $file->move(storage_path('app/csv-import/tmp'), $file->getClientOriginalName());

        return response()->json(['result' => 'success', 'file' => $file->getClientOriginalName()]);
    }

    public function deleteall(NovaRequest $request)
    {
        try {
            $files = Storage::files("csv-import/tmp/");
            Storage::delete($files);
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'e' => $e]);
        }
    }
}
