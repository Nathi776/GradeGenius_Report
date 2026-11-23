<?php

namespace App\Http\Controllers;

use App\Services\ClassMarksImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassMarksImportController extends Controller
{
    /**
     * Download a CSV template with the learners of this class pre-filled.
     */
    public function template(Request $request, int $class): StreamedResponse
    {
        $schoolClass = $request->user()->teachingClasses()->with('learners')->findOrFail($class);

        return response()->streamDownload(function () use ($schoolClass): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ClassMarksImporter::HEADERS);
            foreach ($schoolClass->learners as $learner) {
                fputcsv($out, [$learner->email, 'quiz', 'Example Quiz', '', '4', now()->toDateString(), '1', '', '']);
            }
            fclose($out);
        }, str($schoolClass->name)->slug()->append('-marks-template.csv')->toString(), ['Content-Type' => 'text/csv']);
    }

    /**
     * Import marks for the whole class from an uploaded CSV file.
     */
    public function store(Request $request, ClassMarksImporter $importer, int $class): RedirectResponse
    {
        $schoolClass = $request->user()->teachingClasses()->findOrFail($class);

        $request->validate([
            'marks_file' => ['required', 'file', 'mimes:csv,txt', 'max:512'],
        ]);

        $handle = fopen($request->file('marks_file')->getRealPath(), 'r');
        $result = $importer->import($request->user(), $schoolClass, $handle);
        fclose($handle);

        if ($result['errors'] !== []) {
            return back()->with('import_errors', array_slice($result['errors'], 0, 20));
        }

        return back()->with('status', "Imported {$result['imported']} marks into {$schoolClass->name}.");
    }
}
