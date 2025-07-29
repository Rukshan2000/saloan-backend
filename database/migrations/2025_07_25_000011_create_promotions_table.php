<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['PERCENTAGE', 'FIXED_AMOUNT']);
            $table->string('code')->unique();
            $table->decimal('value', 10, 2);
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->decimal('min_amount', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('promotions');
    }
};
