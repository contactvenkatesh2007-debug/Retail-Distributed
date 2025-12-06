<?php

namespace App\Jobs;

use App\Services\ServiceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $orderData
    ) {
        $this->onQueue('orders');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing order', ['order_id' => $this->orderData['id'] ?? 'unknown']);

        // Check inventory
        $inventoryClient = new ServiceClient('inventory');
        $inventoryCheck = $inventoryClient->post('check', [
            'product_id' => $this->orderData['product_id'] ?? null,
            'quantity' => $this->orderData['quantity'] ?? 1,
        ]);

        if (!$inventoryCheck['success']) {
            throw new \Exception('Inventory check failed: ' . ($inventoryCheck['error'] ?? 'Unknown error'));
        }

        // Process payment
        $paymentClient = new ServiceClient('payment');
        $paymentResult = $paymentClient->post('process', [
            'order_id' => $this->orderData['id'] ?? null,
            'amount' => $this->orderData['amount'] ?? 0,
            'payment_method' => $this->orderData['payment_method'] ?? 'credit_card',
        ]);

        if (!$paymentResult['success']) {
            throw new \Exception('Payment processing failed: ' . ($paymentResult['error'] ?? 'Unknown error'));
        }

        // Update inventory
        $inventoryUpdate = $inventoryClient->post('deduct', [
            'product_id' => $this->orderData['product_id'] ?? null,
            'quantity' => $this->orderData['quantity'] ?? 1,
        ]);

        if (!$inventoryUpdate['success']) {
            Log::warning('Inventory update failed after payment', [
                'order_id' => $this->orderData['id'] ?? 'unknown',
            ]);
        }

        // Send notification
        $notificationClient = new ServiceClient('notification');
        $notificationClient->post('send', [
            'type' => 'order_confirmation',
            'recipient' => $this->orderData['user_email'] ?? null,
            'data' => $this->orderData,
        ]);

        Log::info('Order processed successfully', ['order_id' => $this->orderData['id'] ?? 'unknown']);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Order processing failed', [
            'order_id' => $this->orderData['id'] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);

        // Notify admin or handle failure
        $notificationClient = new ServiceClient('notification');
        $notificationClient->post('send', [
            'type' => 'order_failed',
            'recipient' => config('mail.admin_email', 'admin@example.com'),
            'data' => [
                'order' => $this->orderData,
                'error' => $exception->getMessage(),
            ],
        ]);
    }
}

