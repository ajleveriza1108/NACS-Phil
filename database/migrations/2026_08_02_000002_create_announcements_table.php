<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('announcements', function (Blueprint $table) {
        $table->id(); $table->string('title', 180); $table->string('slug', 190)->unique();
        $table->string('excerpt', 400)->nullable(); $table->text('body'); $table->string('type', 20)->default('info');
        $table->dateTime('starts_at')->nullable(); $table->dateTime('ends_at')->nullable(); $table->dateTime('published_at')->nullable()->index();
        $table->boolean('is_featured')->default(false)->index(); $table->unsignedInteger('sort_order')->default(0); $table->timestamps();
    }); }
    public function down(): void { Schema::dropIfExists('announcements'); }
};
