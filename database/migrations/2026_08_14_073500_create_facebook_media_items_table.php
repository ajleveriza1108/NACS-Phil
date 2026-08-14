<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_media_items', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 220)->unique();
            $table->string('media_type', 20)->default('video')->index();
            $table->text('facebook_url');
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('public_confirmed_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_media_items');
    }
};
