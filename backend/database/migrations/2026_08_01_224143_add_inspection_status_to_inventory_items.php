<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite tesztadatbázisnál nincs szükség módosításra,
        // mert az eredeti create migration már tartalmazza az INSPECTION státuszt.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            DO $$
            DECLARE
                constraint_name text;
            BEGIN
                SELECT conname
                INTO constraint_name
                FROM pg_constraint
                WHERE conrelid = 'inventory_items'::regclass
                  AND contype = 'c'
                  AND pg_get_constraintdef(oid) LIKE '%status%';

                IF constraint_name IS NOT NULL THEN
                    EXECUTE format(
                        'ALTER TABLE inventory_items DROP CONSTRAINT %I',
                        constraint_name
                    );
                END IF;
            END
            $$;
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE inventory_items
            ADD CONSTRAINT inventory_items_status_check
            CHECK (
                status IN (
                    'AVAILABLE',
                    'RENTED',
                    'INSPECTION',
                    'MAINTENANCE',
                    'DAMAGED',
                    'INACTIVE'
                )
            )
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE inventory_items
            DROP CONSTRAINT IF EXISTS inventory_items_status_check
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE inventory_items
            ADD CONSTRAINT inventory_items_status_check
            CHECK (
                status IN (
                    'AVAILABLE',
                    'RENTED',
                    'MAINTENANCE',
                    'DAMAGED',
                    'INACTIVE'
                )
            )
        SQL);
    }
};