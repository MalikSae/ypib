<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospect_events', function (Blueprint $table) {
            // event: seminar/sosialisasi kampus (tanpa affiliator).
            // online: kampanye umum per kampus, dibagikan affiliator lewat ?ref=.
            $table->string('kind', 20)->default('event')->after('campus_id');
            $table->index(['kind', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('prospect_events', function (Blueprint $table) {
            $table->dropIndex(['kind', 'is_active']);
            $table->dropColumn('kind');
        });
    }
};
