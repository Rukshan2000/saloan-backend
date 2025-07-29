<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('beautician_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beautician_id')->constrained('users')->cascadeOnDelete();
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->unique(['beautician_id', 'day_of_week']);
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('beautician_availability');
    }
};
