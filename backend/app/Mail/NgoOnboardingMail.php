<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NgoOnboardingMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $link;
    /**
     * Create a new message instance.
     */
    public function __construct(string $link)
    {
        $this->link = $link;
    }

    public function build()
    {
        $html = '
            <p>Hello,</p>
            <p>Your NGO has been approved. Please onboard using the link below:</p>
            <p><a href="'.$this->link.'">'.$this->link.'</a></p>
            <p>Thank you,<br>HelpRelief Team</p>
        ';

        return $this->subject('HelpRelief NGO Onboarding')
            ->html($html);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ngo Onboarding Mail'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: "
                <p>Hello,</p>
                <p>Your NGO has been approved. Please onboard using the link below:</p>
                <p><a href=\"{$this->link}\">{$this->link}</a></p>
                <p>Thank you,<br>HelpRelief Team</p>
            "
        );
    }


    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
