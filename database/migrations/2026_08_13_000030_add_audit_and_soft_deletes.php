<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('school_events', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('gallery_items', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::create('content_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_name', 150)->nullable();
            $table->string('actor_role', 50)->nullable();
            $table->string('action', 40)->index();
            $table->string('auditable_type', 160)->index();
            $table->unsignedBigInteger('auditable_id')->nullable()->index();
            $table->string('label', 250);
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_audits');

        Schema::table('gallery_items', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('school_events', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('announcements', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};