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
        Schema::table('customer_bill_products', function (Blueprint $table) {
            $table->text('description')->nullable()->after('product_id');
            $table->decimal('packing', 10, 2)->nullable()->after('quantity');
            $table->decimal('total_weight', 10, 2)->nullable()->after('packing');
            $table->decimal('bardana_weight', 10, 2)->nullable()->after('total_weight');
            $table->decimal('net_weight', 10, 2)->nullable()->after('bardana_weight');
            $table->decimal('rate_per_40kg', 12, 2)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_bill_products', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'packing',
                'total_weight',
                'bardana_weight',
                'net_weight',
                'rate_per_40kg',
            ]);
        });
    }
};
