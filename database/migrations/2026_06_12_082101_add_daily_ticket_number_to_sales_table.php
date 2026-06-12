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
        Schema::table('sales', function (Blueprint $table) {
            $table->date('ticket_date')->nullable();
            $table->unsignedInteger('ticket_number')->nullable();
            $table->unique(['ticket_date', 'ticket_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['ticket_date', 'ticket_number']);
            $table->dropColumn(['ticket_date', 'ticket_number']);
        });
    }
};
