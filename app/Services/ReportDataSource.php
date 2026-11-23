<?php

namespace App\Services;

use App\Exceptions\NoReportDataException;
use App\Models\User;
use App\Support\DemoReportData;

/**
 * Where report pages get a learner's raw marks from: the database, with the sample data as a
 * development-only fallback (config reports.demo_fallback).
 */
final class ReportDataSource
{
    public function __construct(private readonly LearnerReportRepository $repository) {}

    /**
     * @return array<string, mixed>|null
     */
    public function forStudentOrNull(User $student): ?array
    {
        $raw = $this->repository->forStudent($student);

        if ($raw !== null) {
            return $raw;
        }

        if (! $this->demoFallbackEnabled()) {
            return null;
        }

        $demo = DemoReportData::forStudent();
        $demo['student']['name'] = $student->name;

        return $demo;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws NoReportDataException
     */
    public function forStudent(User $student): array
    {
        return $this->forStudentOrNull($student) ?? throw new NoReportDataException;
    }

    public function demoFallbackEnabled(): bool
    {
        return (bool) config('reports.demo_fallback');
    }
}
