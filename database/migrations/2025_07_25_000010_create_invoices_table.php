<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->enum('status', ['PENDING', 'PAID', 'OVERDUE', 'CANCELLED']);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('invoices');
    }
};
