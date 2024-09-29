<?php

namespace App\Console\Commands;

use App\Events\BookingEvent;
use App\Http\Controllers\Api\V1\BookingController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class NowServing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nowserving:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Cron Job running at " . now());

        $bookingController = new BookingController();

        Event::dispatch(new BookingEvent($bookingController->updateRequestNowServing()));
    }
}
