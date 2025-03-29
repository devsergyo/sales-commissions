<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The email to be sent.
     *
     * @var \Illuminate\Mail\Mailable
     */
    protected $email;

    /**
     * The recipient's email address.
     *
     * @var string
     */
    protected $recipient;

    /**
     * Number of attempts for the job.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Time to wait between attempts (in seconds).
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Time limit for execution (in seconds).
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Creates a new instance of the job.
     *
     * @param string $recipient
     * @param \Illuminate\Mail\Mailable $email
     * @return void
     */
    public function __construct(string $recipient, Mailable $email)
    {
        $this->recipient = $recipient;
        $this->email = $email;
        $this->onQueue('emails');
    }

    /**
     * Executes the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            Mail::to($this->recipient)->send($this->email);
        }catch (\Exception $e) {
            Log::info($e);
        }

    }
}
