<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_contents')) {
            return;
        }

        Schema::create('site_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('page', 80)->index();
            $table->string('key', 120);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['page', 'key']);
        });

        $defaults = \App\Support\HomeContent::defaults();
        $now = now();

        foreach ($defaults as $key => $value) {
            DB::table('site_contents')->insert([
                'page' => 'home',
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};