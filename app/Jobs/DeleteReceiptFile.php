<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteReceiptFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $donation_number;
    public function __construct($donation_number)
    {
        $this->donation_number = $donation_number;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $donation_number = $this->donation_number;
        try {
            $path = "Donation-Receipt-$donation_number.pdf";

            if(Storage::disk('public_path')->exists($path)) {
                Storage::disk('public_path')->delete($path);
            }
        } catch (\Throwable $th) {
            Log::debug("error delete file receipt => $donation_number");
        }
    }
}
