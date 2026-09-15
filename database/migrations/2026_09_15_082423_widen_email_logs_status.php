<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds "skipped" for when a merchant has email sending turned off in
 * Settings — the delivery job records that it didn't even try, rather
 * than either faking a "sent" status or being absent entirely.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE email_logs DROP CONSTRAINT IF EXISTS email_logs_status_check');
        DB::statement("ALTER TABLE email_logs ADD CONSTRAINT email_logs_status_check CHECK (status IN ('queued','sent','failed','skipped'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE email_logs DROP CONSTRAINT IF EXISTS email_logs_status_check');
        DB::statement("ALTER TABLE email_logs ADD CONSTRAINT email_logs_status_check CHECK (status IN ('queued','sent','failed'))");
    }
};
