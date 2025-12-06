<?php

namespace App\Jobs;

use App\Services\AwsStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $imagePath,
        public int $productId,
        public array $sizes = ['thumbnail', 'medium', 'large']
    ) {
        $this->onQueue('images');
    }

    /**
     * Execute the job.
     */
    public function handle(AwsStorageService $storageService): void
    {
        Log::info('Processing product image', [
            'product_id' => $this->productId,
            'image_path' => $this->imagePath,
        ]);

        try {
            // Download original image
            $originalContent = $storageService->download($this->imagePath);

            if (!$originalContent) {
                throw new \Exception('Failed to download original image');
            }

            // Process different sizes (in a real implementation, you'd use image manipulation library)
            foreach ($this->sizes as $size) {
                $processedPath = "products/{$this->productId}/{$size}/" . basename($this->imagePath);
                
                // Here you would resize/process the image
                // For now, we'll just upload a copy
                $result = $storageService->uploadFile($processedPath, $originalContent, 'public');

                if ($result['success']) {
                    Log::info('Image size processed', [
                        'product_id' => $this->productId,
                        'size' => $size,
                        'path' => $result['path'],
                    ]);
                }
            }

            Log::info('Product image processed successfully', [
                'product_id' => $this->productId,
            ]);
        } catch (\Exception $e) {
            Log::error('Image processing failed', [
                'product_id' => $this->productId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

