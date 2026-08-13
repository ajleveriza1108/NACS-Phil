<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('inquiries', function (Blueprint $table) {
        $table->id(); $table->string('guardian_name', 100); $table->string('email', 150)->nullable(); $table->string('phone', 40)->nullable();
        $table->string('student_name', 100)->nullable(); $table->string('level_interested', 80)->index(); $table->text('message');
        $table->string('status', 30)->default('new')->index(); $table->text('admin_notes')->nullable(); $table->dateTime('privacy_consent_at');
        $table->string('ip_hash', 64)->nullable(); $table->string('user_agent', 500)->nullable(); $table->timestamps();
    }); }
    public function down(): void { Schema::dropIfExists('inquiries'); }
};
