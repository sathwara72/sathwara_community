<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-edited overrides of the lang files (lang/{locale}/messages.php and lang/{locale}.json)
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10);
            $table->string('group', 50)->default('messages'); // 'messages' or 'json'
            $table->string('key', 255);
            $table->text('value');
            $table->timestamps();

            $table->unique(['locale', 'group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
