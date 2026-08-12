<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        // Update existing records with unique UUIDs
        $records = DB::table('daybooks')
            ->whereNull('uuid')
            ->orWhere('uuid', '')
            ->get();

        foreach ($records as $record) {
            DB::table('daybooks')
                ->where('id', $record->id)
                ->update(['uuid' => (string) Str::uuid()]);
        }
    }

    public function down()
    {
        // No down needed
    }
};