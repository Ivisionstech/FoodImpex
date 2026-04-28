<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('daybooks', function (Blueprint $table) {
            if (!Schema::hasColumn('daybooks', 'credit_type')) {
                $table->string('credit_type')->nullable()->after('type');
            }
            if (!Schema::hasColumn('daybooks', 'credit_id')) {
                $table->unsignedBigInteger('credit_id')->nullable()->after('credit_type');
            }
            if (!Schema::hasColumn('daybooks', 'debit_type')) {
                $table->string('debit_type')->nullable()->after('credit_id');
            }
            if (!Schema::hasColumn('daybooks', 'debit_id')) {
                $table->unsignedBigInteger('debit_id')->nullable()->after('debit_type');
            }
        });
    }

    public function down()
    {
        Schema::table('daybooks', function (Blueprint $table) {
            $table->dropColumn(['credit_type', 'credit_id', 'debit_type', 'debit_id']);
        });
    }
};