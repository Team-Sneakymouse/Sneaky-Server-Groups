<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sneaky_server_groups')) {
            Schema::create('sneaky_server_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('sort')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sneaky_server_group_memberships')) {
            Schema::create('sneaky_server_group_memberships', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_group_id');
                $table->unsignedInteger('server_id');
                $table->integer('position')->default(0);
                $table->timestamps();

                $table->foreign('server_group_id')->references('id')->on('sneaky_server_groups')->onDelete('cascade');
                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
                $table->unique('server_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sneaky_server_group_memberships');
        Schema::dropIfExists('sneaky_server_groups');
    }
};
