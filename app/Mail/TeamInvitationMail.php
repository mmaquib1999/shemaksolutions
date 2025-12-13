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
    public string $acceptUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(TeamMember $invitation, string $acceptUrl)
    {
        $this->invitation = $invitation;
        $this->acceptUrl = $acceptUrl;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('You are invited to join the team')
            ->markdown('emails.team_invitation', [
                'invitation' => $this->invitation,
                'acceptUrl' => $this->acceptUrl,
            ]);
    }
}
