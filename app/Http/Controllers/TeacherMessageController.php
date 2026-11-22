<?php

namespace App\Http\Controllers;

use App\Models\TeacherMessage;
use App\Models\User;
use App\Notifications\ParentAcknowledgedNotification;
use App\Notifications\TeacherMessageNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherMessageController extends Controller
{
    /**
     * Display all messages sent by this educator.
     */
    public function index(Request $request): View
    {
        $teacher = $request->user();
        $messages = TeacherMessage::query()
            ->where('teacher_id', $teacher->id)
            ->with(['student', 'parent'])
            ->latest()
            ->paginate(15);

        return view('teacher.messages.index', [
            'messages' => $messages,
            'teacher' => $teacher,
        ]);
    }

    /**
     * Show form to compose a message to a verified parent.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $teacher = $request->user();
        $studentId = $request->query('student_id');

        if (! $studentId) {
            return redirect()->route('teacher.home')->with('error', 'Please select a learner to message their parent.');
        }

        $student = User::where('role', 'student')->find($studentId);
        if (! $student) {
            return redirect()->route('teacher.home')->with('error', 'Learner not found.');
        }

        // Authorise: Educator must teach this learner
        if (! $teacher->teachesStudent($student)) {
            abort(403, 'You are not authorised to message the guardian of a learner not enrolled in your classes.');
        }

        // Verified parents only
        $verifiedParents = $student->parents()->wherePivot('status', 'verified')->get();
        if ($verifiedParents->isEmpty()) {
            return redirect()->route('teacher.home')->with('error', "{$student->name} does not have a verified parent or guardian account linked yet. Notes can only be sent once a parent confirms their link.");
        }

        // Pre-fill metrics from query parameters if available
        $subject = $request->query('subject', 'Mathematics');
        $mark = $request->query('mark');
        $target = $request->query('target', 65);
        $weakTopic = $request->query('weak_topic');

        $templates = [
            'concern' => [
                'subject' => "Academic Concern: {$student->name} ({$subject})",
                'body' => "Dear Guardian,\n\n{$student->name}'s current mark in {$subject} is ".($mark ? "{$mark}%" : 'below expected progress').', which is below the target of '."{$target}%.".($weakTopic ? " We have identified {$weakTopic} as an area needing extra practice." : '')." I recommend revising recent past papers and quizzes.\n\nKind regards,\n{$teacher->name}",
            ],
            'progress_update' => [
                'subject' => "Progress Update: {$student->name} ({$subject})",
                'body' => "Dear Guardian,\n\nI am sharing a routine progress update for {$student->name} in {$subject}. Their current average is ".($mark ? "{$mark}%" : 'on track').", working towards our target of {$target}%. {$student->name} continues to participate actively in class.\n\nKind regards,\n{$teacher->name}",
            ],
            'praise' => [
                'subject' => "Commendation: Outstanding Effort from {$student->name} ({$subject})",
                'body' => "Dear Guardian,\n\nI would like to commend {$student->name} for their dedicated effort in {$subject}. Their current mark of ".($mark ? "{$mark}%" : 'excellent standard')." shows consistent commitment. Please encourage them to maintain this strong momentum!\n\nKind regards,\n{$teacher->name}",
            ],
            'meeting_request' => [
                'subject' => "Consultation Request: {$student->name} ({$subject})",
                'body' => "Dear Guardian,\n\nI would like to arrange a brief discussion regarding {$student->name}'s progress in {$subject} to explore how we can best support their learning together. Please let me know your availability for a short phone call or in-person meeting.\n\nKind regards,\n{$teacher->name}",
            ],
        ];

        return view('teacher.messages.create', [
            'student' => $student,
            'verifiedParents' => $verifiedParents,
            'templates' => $templates,
            'subject' => $subject,
            'mark' => $mark,
            'target' => $target,
        ]);
    }

    /**
     * Store and dispatch message to the verified parent.
     */
    public function store(Request $request): RedirectResponse
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'parent_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', Rule::in([
                TeacherMessage::TYPE_PROGRESS_UPDATE,
                TeacherMessage::TYPE_CONCERN,
                TeacherMessage::TYPE_PRAISE,
                TeacherMessage::TYPE_MEETING_REQUEST,
            ])],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        $student = User::where('role', 'student')->findOrFail($validated['student_id']);
        $parent = User::where('role', 'parent')->findOrFail($validated['parent_id']);

        // Authorise 1: Teacher teaches student
        abort_unless($teacher->teachesStudent($student), 403, 'Educator is not authorised to message about this learner.');

        // Authorise 2: Parent is verified for this student
        $isVerified = DB::table('parent_student_links')
            ->where('parent_id', $parent->id)
            ->where('student_id', $student->id)
            ->where('status', 'verified')
            ->exists();

        abort_unless($isVerified, 403, 'Selected recipient is not a confirmed guardian for this learner.');

        $message = TeacherMessage::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
        ]);

        $parent->notify(new TeacherMessageNotification($message));

        return redirect()->route('teacher.messages.index')
            ->with('status', "Your note regarding {$student->name} has been sent to {$parent->name}.");
    }

    /**
     * Parent views the message and it is marked as read.
     */
    public function showForParent(Request $request, TeacherMessage $message): View
    {
        $parent = $request->user();

        abort_unless($parent->id === $message->parent_id, 403, 'Unauthorized access to message.');

        $message->markAsRead();

        // Also mark matching database notification as read
        $parent->unreadNotifications()
            ->where('data->message_id', $message->id)
            ->get()
            ->markAsRead();

        return view('parent.messages.show', [
            'message' => $message->load(['teacher', 'student']),
            'parent' => $parent,
        ]);
    }

    /**
     * Parent acknowledges or sends quick reply.
     */
    public function acknowledge(Request $request, TeacherMessage $message): RedirectResponse
    {
        $parent = $request->user();

        abort_unless($parent->id === $message->parent_id, 403, 'Unauthorized access to message.');

        if ($message->acknowledged_at !== null) {
            return back()->with('status', 'You have already replied to this note.');
        }

        $validated = $request->validate([
            'quick_reply' => ['nullable', 'string', Rule::in([
                'Thanks, noted',
                'Please call me',
                'Understood, following up at home',
                'Request meeting time',
            ])],
            'custom_reply' => ['nullable', 'string', 'max:255'],
        ]);

        $reply = ! empty($validated['custom_reply'])
            ? trim($validated['custom_reply'])
            : ($validated['quick_reply'] ?? 'Acknowledged');

        $message->update([
            'acknowledged_at' => now(),
            'quick_reply' => $reply,
        ]);

        // Send alert back to educator
        $message->teacher->notify(new ParentAcknowledgedNotification($message, $reply));

        return back()->with('status', "Note acknowledged with response: \"{$reply}\".");
    }
}
