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
        Schema::table('bill_products', function (Blueprint $table) {
            $table->decimal('packing', 10, 2)->nullable()->after('quantity');
            $table->decimal('total_weight', 10, 2)->nullable()->after('packing');
            $table->decimal('bardana_weight', 10, 2)->nullable()->after('total_weight');
            $table->decimal('net_weight', 10, 2)->nullable()->after('bardana_weight');
            $table->decimal('total_price', 12, 2)->nullable()->after('price');
            $table->string('type')->default('bill')->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_products', function (Blueprint $table) {
            $table->dropColumn(['packing', 'total_weight', 'bardana_weight', 'net_weight', 'total_price', 'type']);
        });
    }
};
