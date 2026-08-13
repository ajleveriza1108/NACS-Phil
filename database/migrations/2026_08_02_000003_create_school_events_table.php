<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('school_events', function (Blueprint $table) {
        $table->id(); $table->string('title', 180); $table->string('slug', 190)->unique(); $table->text('description');
        $table->string('venue', 180)->nullable(); $table->dateTime('starts_at')->index(); $table->dateTime('ends_at')->index();
        $table->boolean('is_all_day')->default(false); $table->dateTime('published_at')->nullable()->index();
        $table->string('registration_url', 500)->nullable(); $table->timestamps();
    }); }
    public function down(): void { Schema::dropIfExists('school_events'); }
};
