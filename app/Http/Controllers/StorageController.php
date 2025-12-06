<?php

namespace App\Http\Controllers;

use App\Services\AwsStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StorageController extends Controller
{
    protected AwsStorageService $storageService;

    public function __construct(AwsStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Upload file to AWS S3
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'path' => 'nullable|string',
            'visibility' => 'nullable|in:private,public',
        ]);

        $file = $request->file('file');
        $path = $request->input('path', 'uploads/' . date('Y/m/d'));
        $visibility = $request->input('visibility', 'private');

        $fullPath = $path . '/' . $file->getClientOriginalName();
        $fileContents = file_get_contents($file->getRealPath());

        $result = $this->storageService->uploadFile($fullPath, $fileContents, $visibility);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'url' => $result['url'],
                'path' => $result['path'],
                'key' => $result['key'],
            ], 201);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 500);
    }

    /**
     * Download file from AWS S3
     */
    public function download(string $path): JsonResponse
    {
        $contents = $this->storageService->download($path);

        if ($contents === null) {
            return response()->json([
                'success' => false,
                'error' => 'File not found',
            ], 404);
        }

        return response($contents)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . basename($path) . '"');
    }

    /**
     * Get presigned URL for file
     */
    public function getUrl(Request $request, string $path): JsonResponse
    {
        $expiration = $request->input('expiration', 3600);

        $url = $this->storageService->getUrl($path, $expiration);

        return response()->json([
            'success' => true,
            'url' => $url,
            'expiration' => $expiration,
        ]);
    }

    /**
     * Delete file from AWS S3
     */
    public function delete(string $path): JsonResponse
    {
        $result = $this->storageService->delete($path);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to delete file',
        ], 500);
    }

    /**
     * List files in S3 bucket
     */
    public function listFiles(Request $request): JsonResponse
    {
        $prefix = $request->input('prefix', '');
        $maxKeys = $request->input('max_keys', 1000);

        $files = $this->storageService->listFiles($prefix, $maxKeys);

        return response()->json([
            'success' => true,
            'files' => $files,
            'count' => count($files),
        ]);
    }
}

