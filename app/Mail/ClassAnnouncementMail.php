<?php

namespace App\Mail;

use App\Models\ClassAnnouncement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClassAnnouncementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClassAnnouncement $announcement,
        public User $parent
    ) {}

    public function envelope(): Envelope
    {
        $className = $this->announcement->schoolClass->name;
        $subject = $this->announcement->schoolClass->subject;

        return new Envelope(
            subject: "[GradeGenius] Class Announcement: {$this->announcement->title} ({$className} {$subject})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.class-announcement',
        );
    }
}
