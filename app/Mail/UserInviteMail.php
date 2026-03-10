<?php

namespace App\Mail;

use App\Models\Role;
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
        $this->invite->load('user.role');

        $isStudent = $this->invite->user->role_id === Role::STUDENT;
        $view = $isStudent ? 'emails.student-invite' : 'emails.staff-invite';

        return new Content(
            view: $view,
            with: [
                'invite' => $this->invite,
                'url' => url('/invite/' . $this->invite->token),
            ]
        );
    }
}
