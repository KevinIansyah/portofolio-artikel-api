<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dateTime('published_at')->nullable()->change();
        });

        DB::table('projects')->update([
            'published_at' => null,
            'status' => 'draft'
        ]);
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->date('published_at')->nullable()->change();
        });
    }
};
