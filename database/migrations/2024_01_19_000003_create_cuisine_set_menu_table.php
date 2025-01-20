<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuisine_set_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('set_menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('cuisine_id')->constrained()->onDelete('cascade');
            
            $table->unique(['set_menu_id', 'cuisine_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuisine_set_menu');
    }
};