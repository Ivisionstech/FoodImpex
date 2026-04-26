<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customer_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_transactions', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved'])->default('pending')->after('type');
            }
        });
    }

    public function down()
    {
        Schema::table('customer_transactions', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};