<?php

namespace App\Notifications;

use App\Models\TeacherMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TeacherMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public TeacherMessage $message) {}

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
            'message_id' => $this->message->id,
            'teacher_name' => $this->message->teacher->name,
            'student_name' => $this->message->student->name,
            'student_id' => $this->message->student_id,
            'subject' => $this->message->subject,
            'type' => $this->message->type,
            'preview' => Str::limit($this->message->body, 90),
            'action_url' => route('parent.messages.show', $this->message->id),
        ];
    }
}
