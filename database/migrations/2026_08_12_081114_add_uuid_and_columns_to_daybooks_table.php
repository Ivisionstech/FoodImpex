<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add the uuid column without unique constraint
        if (!Schema::hasColumn('daybooks', 'uuid')) {
            Schema::table('daybooks', function (Blueprint $table) {
                $table->string('uuid')->nullable()->after('id');
            });
        }

        // Update existing records with unique UUIDs using chunk with orderBy
        \DB::table('daybooks')
            ->whereNull('uuid')
            ->orWhere('uuid', '')
            ->orderBy('id')
            ->chunk(100, function ($records) {
                foreach ($records as $record) {
                    \DB::table('daybooks')
                        ->where('id', $record->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });

        // Now add the unique constraint
        Schema::table('daybooks', function (Blueprint $table) {
            // Make uuid not null after populating
            $table->string('uuid')->nullable(false)->change();
            $table->unique('uuid');
        });

        // Add in_hand column if not exists
        if (!Schema::hasColumn('daybooks', 'in_hand')) {
            Schema::table('daybooks', function (Blueprint $table) {
                $table->decimal('in_hand', 18, 2)->default(0)->after('amount');
            });
        }

        // Add approval_status column if not exists
        if (!Schema::hasColumn('daybooks', 'approval_status')) {
            Schema::table('daybooks', function (Blueprint $table) {
                $table->enum('approval_status', ['pending', 'approved'])->default('approved')->after('status');
            });
        }

        // Add expense_date column if not exists
        if (!Schema::hasColumn('daybooks', 'expense_date')) {
            Schema::table('daybooks', function (Blueprint $table) {
                $table->timestamp('expense_date')->nullable()->after('transaction_date');
            });
        }

        // Add customer_transaction_id column if not exists
        if (!Schema::hasColumn('daybooks', 'customer_transaction_id')) {
            Schema::table('daybooks', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_transaction_id')->nullable()->after('reference');
            });
        }

        // Add vendor_transaction_id column if not exists
        if (!Schema::hasColumn('daybooks', 'vendor_transaction_id')) {
            Schema::table('daybooks', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_transaction_id')->nullable()->after('customer_transaction_id');
            });
        }

        // Add expense_id column if not exists
        if (!Schema::hasColumn('daybooks', 'expense_id')) {
            Schema::table('daybooks', function (Blueprint $table) {
                $table->unsignedBigInteger('expense_id')->nullable()->after('vendor_transaction_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daybooks', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumnIfExists('uuid');
            $table->dropColumnIfExists('in_hand');
            $table->dropColumnIfExists('approval_status');
            $table->dropColumnIfExists('expense_date');
            $table->dropColumnIfExists('customer_transaction_id');
            $table->dropColumnIfExists('vendor_transaction_id');
            $table->dropColumnIfExists('expense_id');
        });
    }
};