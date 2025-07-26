<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;

class StorageController extends Controller
{
    private $gcsPath;

    public function __construct()
    {
        // Define the path to the gcsfuse mount point
        $this->gcsPath = storage_path('app/gcs');
    }

    public function index()
    {
        $files = [];
        if (File::exists($this->gcsPath)) {
            $fileList = File::files($this->gcsPath);
            foreach ($fileList as $file) {
                $files[] = $file->getFilename();
            }
        }
        
        return view('storage', ['files' => $files]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'filename' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $filePath = $this->gcsPath . '/' . $request->input('filename');
        File::put($filePath, $request->input('content') ?? '');

        return redirect()->route('storage.index')->with('success', 'File created successfully!');
    }

    public function delete(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $filePath = $this->gcsPath . '/' . $request->input('filename');

        if (File::exists($filePath)) {
            File::delete($filePath);
            return redirect()->route('storage.index')->with('success', 'File deleted successfully!');
        }

        return redirect()->route('storage.index')->with('error', 'File not found.');
    }
}