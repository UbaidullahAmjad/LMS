<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendZoomLinkCancelEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $topic;
    public $course;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($topic,$course)
    {
        $this->topic = $topic;
        $this->course = $course;


    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Meeting Cancelled")
        ->view('zoom.zoomlinkcancelemail',[
            'topic' => $this->topic,
            'course' => $this->course,
        ]);
    }
}
