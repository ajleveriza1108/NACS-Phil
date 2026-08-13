<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('gallery_items', function (Blueprint $table) {
        $table->id(); $table->string('title', 180); $table->string('category', 80)->index(); $table->string('image_path', 500);
        $table->string('alt_text', 250); $table->text('caption')->nullable(); $table->date('taken_at')->nullable();
        $table->boolean('is_published')->default(false)->index(); $table->unsignedInteger('sort_order')->default(0);
        $table->dateTime('consent_confirmed_at')->nullable()->index(); $table->string('photographer_credit', 180)->nullable(); $table->timestamps();
    }); }
    public function down(): void { Schema::dropIfExists('gallery_items'); }
};
