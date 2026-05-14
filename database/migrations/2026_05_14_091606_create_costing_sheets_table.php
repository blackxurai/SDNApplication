<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('costing_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('project_name');
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('prepared_by')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->decimal('overhead_percent', 5, 2)->default(0);
            $table->decimal('profit_margin_percent', 5, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->enum('status', ['draft', 'finalized', 'converted'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_sheets');
    }
};
