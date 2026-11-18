<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->foreignUuid('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->restrictOnDelete();
            // Circular revision pointers are constrained in a later migration.
            $table->uuid('current_published_revision_id')->nullable()->index();
            $table->uuid('working_revision_id')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->index(['category_id', 'published_at']);
            $table->index(['author_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
