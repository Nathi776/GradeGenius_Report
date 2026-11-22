<?php

namespace App\Notifications;

use App\Models\ClassAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ClassAnnouncementNotification extends Notification
{
    use Queueable;

    public function __construct(public ClassAnnouncement $announcement)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'class_id' => $this->announcement->school_class_id,
            'class_name' => $this->announcement->schoolClass->name,
            'subject' => $this->announcement->schoolClass->subject,
            'teacher_name' => $this->announcement->teacher->name,
            'title' => $this->announcement->title,
            'priority' => $this->announcement->priority,
            'type' => 'class_announcement',
            'preview' => Str::limit($this->announcement->body, 90),
            'action_url' => route('parent.announcements.show', $this->announcement->id),
        ];
    }
}
