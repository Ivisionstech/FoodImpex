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
    Schema::table('customer_bills', function (Blueprint $table) {
        // We add the column that the error is complaining about
        $table->decimal('grand_total', 15, 2)->after('payment_terms')->default(0);
    });
}

public function down(): void
{
    Schema::table('customer_bills', function (Blueprint $table) {
        $table->dropColumn('grand_total');
    });
}
};
