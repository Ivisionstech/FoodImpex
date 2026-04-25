<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('products', function (Blueprint $table) {
        // Only add if they don't exist to avoid errors
        if (!Schema::hasColumn('products', 'net_weight')) {
            $table->decimal('net_weight', 10, 2)->default(0)->after('name');
        }
        if (!Schema::hasColumn('products', 'price_40kg')) {
            $table->decimal('price_40kg', 18, 2)->default(0)->after('net_weight');
        }
        if (!Schema::hasColumn('products', 'stock')) {
            $table->decimal('stock', 10, 2)->default(0)->after('price_40kg');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
