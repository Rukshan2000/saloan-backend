<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('service_beauticians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('beautician_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['service_id', 'beautician_id']);
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('service_beauticians');
    }
};
