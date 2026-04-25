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
        if (!Schema::hasColumn('customer_bills', 'type')) {
            Schema::table('customer_bills', function (Blueprint $table) {
                $table->string('type')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('customer_bills', 'type')) {
            Schema::table('customer_bills', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
