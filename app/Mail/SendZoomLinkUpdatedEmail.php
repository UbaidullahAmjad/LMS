<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendZoomLinkUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $link;
    public $topic;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($link,$topic)
    {
        $this->link = $link;
        $this->topic = $topic;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->topic)
        ->view('zoom.zoomlinkupdateemail',[
            'link' => $this->link
        ]);
    }
}
