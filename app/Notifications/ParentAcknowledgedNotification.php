<?php

namespace App\Notifications;

use App\Models\TeacherMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParentAcknowledgedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TeacherMessage $message,
        public string $reply
    ) {}

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
            'student_name' => $this->message->student->name,
            'parent_name' => $this->message->parent->name,
            'type' => 'parent_acknowledgement',
            'quick_reply' => $this->reply,
            'subject' => "Parent response from {$this->message->parent->name} regarding {$this->message->student->name}",
            'preview' => "Guardian reply: \"{$this->reply}\"",
            'action_url' => route('teacher.messages.index'),
        ];
    }
}
