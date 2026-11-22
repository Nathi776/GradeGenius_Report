<?php

namespace App\Http\Controllers;

use App\Mail\ClassAnnouncementMail;
use App\Models\ClassAnnouncement;
use App\Models\User;
use App\Notifications\ClassAnnouncementNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ClassAnnouncementController extends Controller
{
    /**
     * List announcements this educator has published.
     */
    public function index(Request $request): View
    {
        $announcements = ClassAnnouncement::query()
            ->where('teacher_id', $request->user()->id)
            ->with('schoolClass')
            ->latest()
            ->paginate(15);

        return view('teacher.announcements.index', ['announcements' => $announcements]);
    }

    /**
     * Show the form for publishing an announcement to one of the educator's classes.
     */
    public function create(Request $request): View
    {
        return view('teacher.announcements.create', [
            'classes' => $request->user()->teachingClasses()->withCount('learners')->orderBy('name')->get(),
        ]);
    }

    /**
     * Publish an announcement to the confirmed guardians of a class.
     */
    public function store(Request $request): RedirectResponse
    {
        $teacher = $request->user();

        $validated = $request->validate([
            'school_class_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:3000'],
            'priority' => ['required', Rule::in([
                ClassAnnouncement::PRIORITY_NORMAL,
                ClassAnnouncement::PRIORITY_IMPORTANT,
                ClassAnnouncement::PRIORITY_URGENT,
            ])],
        ]);

        $schoolClass = $teacher->teachingClasses()->find($validated['school_class_id']);
        abort_if($schoolClass === null, 403, 'You can only announce to classes you teach.');

        $guardians = User::query()
            ->where('role', 'parent')
            ->whereHas('students', function ($query) use ($schoolClass): void {
                $query->where('parent_student_links.status', 'verified')
                    ->whereIn('users.id', $schoolClass->learners()->select('users.id'));
            })
            ->get();

        if ($guardians->isEmpty()) {
            return back()->withInput()->withErrors([
                'school_class_id' => 'No confirmed guardians are linked to learners in this class yet.',
            ]);
        }

        $announcement = ClassAnnouncement::create([
            'school_class_id' => $schoolClass->id,
            'teacher_id' => $teacher->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'priority' => $validated['priority'],
            'recipients_count' => $guardians->count(),
        ]);

        foreach ($guardians as $guardian) {
            $guardian->notify(new ClassAnnouncementNotification($announcement));
            Mail::to($guardian->email)->send(new ClassAnnouncementMail($announcement, $guardian));
        }

        return redirect()->route('teacher.announcements.index')
            ->with('status', "Announcement sent to {$guardians->count()} guardian(s) of {$schoolClass->name}.");
    }

    /**
     * Parent reads an announcement for a class one of their confirmed children attends.
     */
    public function showForParent(Request $request, ClassAnnouncement $announcement): View
    {
        $parent = $request->user();

        $hasChildInClass = $parent->linkedChildren()
            ->whereIn('users.id', $announcement->schoolClass->learners()->select('users.id'))
            ->exists();

        abort_unless($hasChildInClass, 403, 'This announcement is not for your family.');

        $parent->unreadNotifications()
            ->where('data->announcement_id', $announcement->id)
            ->get()
            ->markAsRead();

        return view('parent.announcements.show', [
            'announcement' => $announcement->load(['schoolClass', 'teacher']),
        ]);
    }
}
