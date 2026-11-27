<?php

namespace App\Http\Controllers;

use App\Services\ParentTelemetryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ParentActivityController extends Controller
{
    /**
     * Sign-in sessions and page visits of one confirmed child, only when that child has chosen to share them.
     */
    public function __invoke(Request $request, ParentTelemetryService $telemetry): View
    {
        $children = $request->user()->linkedChildren()->orderBy('name')->get();
        $requested = $request->query('child');

        if ($requested !== null) {
            abort_unless(filter_var($requested, FILTER_VALIDATE_INT) !== false, 404);
        }

        $selected = $requested === null ? $children->first() : $children->firstWhere('id', (int) $requested);
        abort_if($requested !== null && $selected === null, 404, 'Unauthorized access to child.');

        return view('reports.parent-activity', [
            'children' => $children->mapWithKeys(fn ($child): array => [(string) $child->id => $child->name])->all(),
            'selectedChild' => $selected,
            'learner' => $selected === null ? null : $telemetry->forStudent($selected),
        ]);
    }
}
