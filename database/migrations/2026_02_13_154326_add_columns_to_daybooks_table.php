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
    Schema::table('daybooks', function (Blueprint $table) {
        if (!Schema::hasColumn('daybooks', 'status')) {
            $table->integer('status')->default(1)->after('amount');
        }

          if (!Schema::hasColumn('daybooks', 'transaction_date')) {
            $table->timestamp('transaction_date')->nullable();
        }

        if (!Schema::hasColumn('daybooks', 'type')) {
            $table->string('type')->nullable();
        }

        if (!Schema::hasColumn('daybooks', 'vendor_transaction_id')) {
            $table->string('vendor_transaction_id')->nullable();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daybooks', function (Blueprint $table) {
            //
        });
    }
};
