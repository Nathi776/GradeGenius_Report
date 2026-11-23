<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

/**
 * Thrown when a report page has no recorded marks to show. Renders a friendly empty state.
 */
class NoReportDataException extends RuntimeException
{
    public function __construct(public readonly string $reason = 'no_marks')
    {
        parent::__construct('There are no recorded marks to report on yet.');
    }

    public function render(Request $request): Response
    {
        $isParent = $request->user()?->role === 'parent';

        return response()->view('reports.no-data', [
            'isParent' => $isParent,
            'reason' => $this->reason,
        ]);
    }
}
