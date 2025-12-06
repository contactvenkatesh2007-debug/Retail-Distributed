<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\ProcessOrderJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        ProcessOrderJob::dispatch($event->orderData);
    }
}

