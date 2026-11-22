<?php

namespace App\Http\Controllers;

use App\Services\TeacherDashboardService;
use App\Services\TeacherLearnerPresenceService;
use App\Support\AcademicTermSelection;
use App\Support\DemoReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        TeacherDashboardService $dashboard,
        TeacherLearnerPresenceService $presence,
    ): View {
        $teacher = $request->user();
        $dbClasses = $teacher->teachingClasses()->with('learners')->get();

        if ($dbClasses->isNotEmpty()) {
            $data = [
                'teacher' => ['name' => $teacher->name],
                'terms' => ['Term 1', 'Term 2', 'Term 3', 'Term 4'],
                'classes' => [],
            ];
            foreach ($dbClasses as $sc) {
                $learners = [];
                foreach ($sc->learners as $l) {
                    $learners[] = [
                        'id' => (string) $l->id,
                        'user_id' => $l->id,
                        'name' => $l->name,
                        'term_averages' => [60, 65, 68, 70],
                    ];
                }
                $data['classes'][] = [
                    'key' => 'class-'.$sc->id,
                    'name' => $sc->name,
                    'subject' => $sc->subject,
                    'grade' => $sc->grade,
                    'target' => $sc->target ?? 65,
                    'learners' => $learners,
                ];
            }
        } else {
            $data = DemoReportData::classesForTeacher();
        }

        $learnerUserIds = [];
        foreach ($data['classes'] as $class) {
            foreach ($class['learners'] as $learner) {
                if (isset($learner['user_id'])) {
                    $learnerUserIds[] = $learner['user_id'];
                }
            }
        }

        $termIndex = AcademicTermSelection::fromRequest($request, $data['terms']);
        $data['teacher']['name'] = $request->user()->name;

        return view('reports.teacher-home', [
            'dashboard' => $dashboard->build($data),
            'learnerPresence' => $presence->forUserIds($learnerUserIds),
            'terms' => $data['terms'],
            'selectedTerm' => AcademicTermSelection::value($termIndex),
            'selectedTermIndex' => $termIndex ?? count($data['terms']) - 1,
            'hideWorkspaceTermSelector' => true,
        ]);
    }
}
