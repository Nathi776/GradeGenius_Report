<?php

namespace App\Http\Middleware;

use App\Services\StudentActivityTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordStudentActivity
{
    public function __construct(private StudentActivityTracker $activity) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->user();

        if ($response->isSuccessful() && $user !== null) {
            $this->activity->recordPageVisit($request, $user);
        }

        return $response;
    }
}
