<?php

namespace App\Mail;

use App\Models\UserInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public UserInvite $invite
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Complete seu cadastro'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-invite',
            with: [
                'invite' => $this->invite,
                'url' => config('app.backend_url') . '/invite/' . $this->invite->token,
            ]
        );
    }
}
