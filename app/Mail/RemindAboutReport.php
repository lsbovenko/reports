<?php

namespace App\Mail;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemindAboutReport extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    protected $remindDate;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Carbon $remindDate)
    {
        $this->user = $user;
        $this->remindDate = $remindDate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Напоминание об отчёте за ' . $this->remindDate->format('m/d'))
            ->view('email.report_reminder')
            ->with([
                'user' => $this->user,
                'date' => $this->remindDate,
            ]);
    }
}
