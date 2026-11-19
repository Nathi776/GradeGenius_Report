<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class TeacherLearnerPresenceService
{
    private const ONLINE_WINDOW_MINUTES = 5;

    /**
     * @param  array<int, int|string>  $userIds
     * @return array<int, array{state: string, label: string, last_active: ?string}>
     */
    public function forUserIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(
            array_map('intval', $userIds),
            fn (int $id): bool => $id > 0,
        )));

        if ($userIds === []) {
            return [];
        }

        if (config('session.driver') !== 'database') {
            return array_fill_keys($userIds, [
                'state' => 'unavailable',
                'label' => 'Unavailable',
                'last_active' => null,
            ]);
        }

        $studentIds = DB::table('users')
            ->whereIn('id', $userIds)
            ->where('role', 'student')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
        $table = (string) config('session.table', 'sessions');
        $latestSessions = $studentIds === []
            ? collect()
            : DB::table($table)
                ->select('user_id')
                ->selectRaw('MAX(last_activity) as last_activity')
                ->whereIn('user_id', $studentIds)
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');
        $onlineAfter = now()->subMinutes(self::ONLINE_WINDOW_MINUTES)->timestamp;

        $presence = [];
        foreach ($userIds as $userId) {
            if (! in_array($userId, $studentIds, true)) {
                $presence[$userId] = [
                    'state' => 'unavailable',
                    'label' => 'Unlinked',
                    'last_active' => null,
                ];

                continue;
            }

            $lastActivity = $latestSessions->get($userId)?->last_activity;
            $isOnline = $lastActivity !== null && (int) $lastActivity >= $onlineAfter;

            $presence[$userId] = [
                'state' => $isOnline ? 'online' : 'offline',
                'label' => $isOnline ? 'Online' : 'Offline',
                'last_active' => $lastActivity === null
                    ? null
                    : Carbon::createFromTimestamp((int) $lastActivity)->diffForHumans(),
            ];
        }

        return $presence;
    }
}
