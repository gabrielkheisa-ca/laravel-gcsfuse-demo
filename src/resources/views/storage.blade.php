<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCS Fuse Test</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f9; color: #333; margin: 0; padding: 2em; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 2em; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2 { color: #555; }
        form { margin-bottom: 2em; }
        input, textarea { width: 100%; padding: 8px; margin-bottom: 1em; border-radius: 4px; border: 1px solid #ddd; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .file-list { list-style: none; padding: 0; }
        .file-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .file-item:last-child { border-bottom: none; }
        .delete-btn { background-color: #dc3545; }
        .delete-btn:hover { background-color: #c82333; }
        .alert { padding: 1em; margin-bottom: 1em; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laravel GCS Fuse Test</h1>
        <p>This interface interacts directly with a GCS bucket mounted at <code>storage/app/gcs</code>.</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <h2>Create a New File</h2>
        <form action="{{ route('storage.create') }}" method="POST">
            @csrf
            <input type="text" name="filename" placeholder="Enter filename (e.g., test.txt)" required>
            <textarea name="content" rows="4" placeholder="Enter file content"></textarea>
            <button type="submit">Create File</button>
        </form>

        <h2>Files in Bucket: <code>{{ env('GCS_BUCKET_NAME') }}</code></h2>
        @if (empty($files))
            <p>No files found in the bucket.</p>
        @else
            <ul class="file-list">
                @foreach ($files as $file)
                    <li class="file-item">
                        <span>{{ $file }}</span>
                        <form action="{{ route('storage.delete') }}" method="POST">
                            @csrf
                            <input type="hidden" name="filename" value="{{ $file }}">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</body>
</html>