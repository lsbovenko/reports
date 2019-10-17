<?php

namespace App\Mail;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OvertimeReport extends Mailable
{
    use Queueable, SerializesModels;

    protected $periodFrom;

    protected $periodTill;

    protected $data;


    public function __construct(array $data, Carbon $from, Carbon $till)
    {
        $this->data = $data;
        $this->periodFrom = $from;
        $this->periodTill = $till;
        app()->setLocale(config('app.fallback_locale'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = $this->periodFrom;
        $till = $this->periodTill;
        //print_r($this->data); exit;
        return $this
            ->subject(trans('reports.overtime_report', ['from' => $from->format('Y-m-d'), 'till' => $till->format('Y-m-d')]))
            ->view('email.report_overtime')
            ->with([
                'data' => $this->data,
            ]);
    }
}
