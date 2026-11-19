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
        // TODO: load the signed-in teacher's assigned classes and enrolled learners from the database.
        // A teacher must only ever see classes and learners they are authorised for.
        $data = DemoReportData::classesForTeacher();
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
