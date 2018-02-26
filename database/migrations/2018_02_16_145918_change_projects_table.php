<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_active')->default(1);
            $table->unsignedInteger('parent_id')->nullable();
            $table->float('rate')->default(0);
            $table->foreign('parent_id')
                ->references('id')
                ->on('projects')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->dropUnique('projects_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
