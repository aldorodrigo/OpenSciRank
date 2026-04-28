<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $map = [
        'products' => [
            'name',
            'description',
        ],
    ];

    public function up(): void
    {
        foreach (array_keys($this->map) as $table) {
            if (! Schema::hasColumn($table, 'primary_locale')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('primary_locale', 2)->default('es')->after('id');
                });
                DB::table($table)->whereNull('primary_locale')->update(['primary_locale' => 'es']);
            }
        }

        foreach ($this->map as $table => $columns) {
            foreach ($columns as $col) {
                if (! Schema::hasColumn($table, $col)) {
                    continue;
                }

                $type = strtolower(Schema::getColumnType($table, $col));
                if (in_array($type, ['json', 'jsonb'], true)) {
                    continue;
                }

                $tempCol = $col . '_json_tmp';

                if (! Schema::hasColumn($table, $tempCol)) {
                    Schema::table($table, function (Blueprint $t) use ($tempCol) {
                        $t->json($tempCol)->nullable();
                    });
                }

                DB::statement("
                    UPDATE `{$table}`
                    SET `{$tempCol}` = JSON_OBJECT(COALESCE(`primary_locale`, 'es'), `{$col}`)
                    WHERE `{$col}` IS NOT NULL
                ");

                Schema::table($table, function (Blueprint $t) use ($col) {
                    $t->dropColumn($col);
                });

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
                        if ($col === 'description') {
                            $t->text($tempCol)->nullable();
                        } else {
                            $t->string($tempCol)->nullable();
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

                $columnType = $col === 'description' ? 'TEXT' : 'VARCHAR(255)';
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
