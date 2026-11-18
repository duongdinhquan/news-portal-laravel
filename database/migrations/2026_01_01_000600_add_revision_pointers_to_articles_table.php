<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('current_published_revision_id')->references('id')->on('article_revisions')->nullOnDelete();
            $table->foreign('working_revision_id')->references('id')->on('article_revisions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['current_published_revision_id']);
            $table->dropForeign(['working_revision_id']);
        });
    }
};
