<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

#[Signature('app:prune-notifications {--read-days=90 : Delete read notifications older than this many days} {--unread-days=365 : Delete unread notifications older than this many days}')]
#[Description('Delete old in-app notifications')]
class PruneNotifications extends Command
{
    public function handle(): int
    {
        $read = DatabaseNotification::query()
            ->whereNotNull('read_at')
            ->where('created_at', '<', now()->subDays((int) $this->option('read-days')))
            ->delete();

        $unread = DatabaseNotification::query()
            ->whereNull('read_at')
            ->where('created_at', '<', now()->subDays((int) $this->option('unread-days')))
            ->delete();

        $this->info("Deleted {$read} read and {$unread} unread notifications.");

        return self::SUCCESS;
    }
}
