<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->integer('duration');
            $table->unique(['appointment_id', 'service_id']);
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('appointment_services');
    }
};
