<?php

namespace App\Services;

/**
 * Parent view of a child's report.
 *
 * Wraps StudentReportService so a parent always sees exactly the numbers the learner sees, then adds
 * what a parent needs: a plain-language summary, one suggested next step, "needs support" / "going
 * well" lists, ideas for home, and recent assessment activity.
 *
 * "Activity" here means quizzes and past papers completed. GradeGenius does not record study time,
 * so nothing in this report claims to measure it.
 */
final class ParentReportService
{
    private const ACTIVITY_WEEKS = 4;

    public function __construct(private StudentReportService $students) {}

    public function build(array $raw, ?string $today = null): array
    {
        $today ??= date('Y-m-d');
        $report = $this->students->build($raw);
        $first = explode(' ', trim($report['student']['name']))[0];

        $subjects = array_map(fn ($s) => $this->decorate($s, $report['assessments'], $first), $report['subjects']);

        $needsSupport = array_values(array_filter($subjects, fn ($s) => $s['target_status']['state'] !== 'met'));
        usort($needsSupport, fn ($a, $b) => ($b['target'] - $b['target_status']['state'] === 'met' ? 0 : $b['current']) <=> ($a['target'] - $a['target_status']['state'] === 'met' ? 0 : $a['current']));
        // In the user's code: usort($needsSupport, fn ($a, $b) => ($b['target'] - $b['current']) <=> ($a['target'] - $a['current']));
        usort($needsSupport, fn ($a, $b) => ($b['target'] - $b['current']) <=> ($a['target'] - $a['current']));

        $supportKeys = array_column($needsSupport, 'key');
        $goingWell = array_values(array_filter(
            $subjects,
            fn ($s) => ! in_array($s['key'], $supportKeys, true)
                && ($s['target_status']['state'] === 'met' || $s['change'] >= 5)
        ));
        usort($goingWell, fn ($a, $b) => $b['change'] <=> $a['change']);

        return array_merge($report, [
            'first_name' => $first,
            'subjects' => $subjects,
            'needs_support' => $needsSupport,
            'going_well' => $goingWell,
            'summary' => $this->summary($first, $report['overall']),
            'next_step' => $this->nextStep($first, $needsSupport),
            'activity' => $this->activity($report['assessments'], $today),
            'recent' => array_slice($report['assessments'], 0, 6),
        ]);
    }

    private function decorate(array $s, array $assessments, string $first): array
    {
        $steps = [];
        foreach (array_slice($s['attention'], 0, 2) as $t) {
            $steps[] = "Go over the {$t['name']} notes together and try a few practice questions.";
        }
        if ($s['target_status']['state'] !== 'met') {
            $steps[] = "Ask {$first} what they find hardest in {$s['name']}, then check with the teacher.";
        }
        if (! $steps) {
            $steps[] = "Keep up regular practice. {$first} is doing well in {$s['name']}.";
        }

        return $s + [
            'status_label' => ['met' => 'On goal', 'close' => 'Almost at goal', 'behind' => 'Needs support'][$s['target_status']['state']],
            'weakest' => $s['attention'][0] ?? null,
            'recent' => array_slice(array_values(array_filter($assessments, fn ($a) => $a['subject_key'] === $s['key'])), 0, 3),
            'home_steps' => $steps,
        ];
    }

    private function summary(string $first, array $o): string
    {
        $pts = abs($o['change']).' '.(abs($o['change']) === 1 ? 'point' : 'points');
        $move = $o['change'] > 0 ? "up {$pts} since {$o['first_term']}"
            : ($o['change'] < 0 ? "down {$pts} since {$o['first_term']}" : "unchanged since {$o['first_term']}");
        $verb = $o['subject_count'] === 1 ? 'subject is' : 'subjects are';

        return "{$first} is averaging {$o['current']}%, {$move}. {$o['at_target']} of {$o['subject_count']} {$verb} at goal.";
    }

    private function nextStep(string $first, array $needsSupport): string
    {
        $focus = $needsSupport[0] ?? null;
        if ($focus === null) {
            return "{$first} is meeting every goal. Keep up regular practice and celebrate the progress.";
        }
        $t = $focus['weakest'];

        return $t
            ? "Encourage {$first} to spend a little time on {$t['name']} in {$focus['name']} ({$t['mastery']}% mastery): go over the notes together and try some practice questions."
            : "Talk to {$first} about how {$focus['name']} is going and ask the teacher what would help most.";
    }

    /** Quizzes and past papers completed recently, based on completion dates only. */
    private function activity(array $assessments, string $today): array
    {
        $now = strtotime($today.' UTC');
        $daysAgo = fn (array $a): int => intdiv($now - strtotime($a['date'].' UTC'), 86400);
        $count = fn (int $from, int $to): int => count(array_filter(
            $assessments,
            fn ($a) => $daysAgo($a) >= $to && $daysAgo($a) < $from
        ));

        $completed = $count(30, 0);
        $previous = $count(60, 30);
        $weeksActive = 0;
        for ($w = 0; $w < self::ACTIVITY_WEEKS; $w++) {
            $weeksActive += $count(7 * ($w + 1), 7 * $w) > 0 ? 1 : 0;
        }

        $last = null;
        foreach ($assessments as $a) {   // newest first
            if ($daysAgo($a) >= 0) {
                $last = $a;
                break;
            }
        }
        $ago = $last ? $daysAgo($last) : null;

        return [
            'completed_30d' => $completed,
            'completed_prev_30d' => $previous,
            'delta' => $completed - $previous,
            'weeks_active' => $weeksActive,
            'weeks_total' => self::ACTIVITY_WEEKS,
            'last_label' => $last['date_label'] ?? null,
            'last_ago' => $ago === null ? null : ($ago === 0 ? 'Today' : ($ago === 1 ? 'Yesterday' : "{$ago} days ago")),
        ];
    }
}
