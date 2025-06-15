<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $htmlContent;

    public function __construct($subjectLine, $htmlContent)
    {
        $this->subjectLine = $subjectLine;
        $this->htmlContent = $htmlContent;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
                    ->view('emails.custom-message')
                    ->with([
                        'htmlContent' => $this->htmlContent
                    ]);
    }
}
