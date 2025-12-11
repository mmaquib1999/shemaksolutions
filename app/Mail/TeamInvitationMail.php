<?php

namespace App\Mail;

use App\Models\TeamMember;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public TeamMember $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(TeamMember $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('You are invited to join the team')
            ->markdown('emails.team_invitation', [
                'invitation' => $this->invitation,
                'acceptUrl' => $this->buildAcceptUrl(),
            ]);
    }

    protected function buildAcceptUrl(): string
    {
        $base = config('app.url');

        return rtrim($base, '/') . '/register?invite_token=' . $this->invitation->invitation_token;
    }
}
