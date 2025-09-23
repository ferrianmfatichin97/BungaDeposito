<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DepositoReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $depositos;
    public $reminder;

    public function __construct($depositos, $reminder)
    {
        $this->depositos = $depositos;
        $this->reminder = $reminder;
    }

    public function build()
    {
        return $this->subject('Reminder Deposito Jatuh Tempo')
                    ->markdown('emails.deposito.reminder')
                    ->with([
                        'depositos' => $this->depositos,
                        'reminder' => $this->reminder,
                    ]);
    }
}
