<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Mapping of tables to translatable columns.
     */
    private array $map = [
        'cms_posts' => [
            'title',
            'excerpt',
            'content',
        ],
    ];

    public function up(): void
    {
        // 1. Add primary_locale (idempotent)
        foreach (array_keys($this->map) as $table) {
            if (! Schema::hasColumn($table, 'primary_locale')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('primary_locale', 2)->default('es')->after('id');
                });
                DB::table($table)->whereNull('primary_locale')->update(['primary_locale' => 'es']);
            }
        }

        // 2. Convert each translatable column to JSON via temp column.
        foreach ($this->map as $table => $columns) {
            foreach ($columns as $col) {
                if (! Schema::hasColumn($table, $col)) {
                    continue;
                }

                $type = strtolower(Schema::getColumnType($table, $col));

                // If already JSON, skip
                if (in_array($type, ['json', 'jsonb'], true)) {
                    continue;
                }

                $tempCol = $col . '_json_tmp';

                // a) Add temp JSON column
                if (! Schema::hasColumn($table, $tempCol)) {
                    Schema::table($table, function (Blueprint $t) use ($tempCol) {
                        $t->json($tempCol)->nullable();
                    });
                }

                // b) Copy values, wrapping each non-null value as {"<primary_locale>": "value"}
                //    JSON_OBJECT escapes strings/HTML correctly natively.
                DB::statement("
                    UPDATE `{$table}`
                    SET `{$tempCol}` = JSON_OBJECT(COALESCE(`primary_locale`, 'es'), `{$col}`)
                    WHERE `{$col}` IS NOT NULL
                ");

                // c) Drop original column
                Schema::table($table, function (Blueprint $t) use ($col) {
                    $t->dropColumn($col);
                });

                // d) Rename temp to original
                DB::statement("ALTER TABLE `{$table}` CHANGE `{$tempCol}` `{$col}` JSON NULL");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->map as $table => $columns) {
            foreach ($columns as $col) {
                if (! Schema::hasColumn($table, $col)) {
                    continue;
                }

                $type = strtolower(Schema::getColumnType($table, $col));
                if (! in_array($type, ['json', 'jsonb'], true)) {
                    continue;
                }

                $tempCol = $col . '_text_tmp';

                if (! Schema::hasColumn($table, $tempCol)) {
                    Schema::table($table, function (Blueprint $t) use ($tempCol, $col) {
                        // content can be very long; use longText to be safe
                        if ($col === 'content') {
                            $t->longText($tempCol)->nullable();
                        } else {
                            $t->text($tempCol)->nullable();
                        }
                    });
                }

                DB::statement("
                    UPDATE `{$table}`
                    SET `{$tempCol}` = JSON_UNQUOTE(JSON_EXTRACT(`{$col}`, CONCAT('$.\"', COALESCE(`primary_locale`, 'es'), '\"')))
                    WHERE `{$col}` IS NOT NULL
                ");

                Schema::table($table, function (Blueprint $t) use ($col) {
                    $t->dropColumn($col);
                });

                $columnType = $col === 'content' ? 'LONGTEXT' : 'TEXT';
                DB::statement("ALTER TABLE `{$table}` CHANGE `{$tempCol}` `{$col}` {$columnType} NULL");
            }
        }

        foreach (array_keys($this->map) as $table) {
            if (Schema::hasColumn($table, 'primary_locale')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('primary_locale');
                });
            }
        }
    }
};
