<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToChatTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add foreign key constraints only if tables exist
        if (Schema::hasTable('groups') && Schema::hasTable('users')) {
            Schema::table('groups', function (Blueprint $table) {
                if (!Schema::hasColumn('groups', 'groups_created_by_foreign')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }

        if (Schema::hasTable('group_members') && Schema::hasTable('groups') && Schema::hasTable('users')) {
            Schema::table('group_members', function (Blueprint $table) {
                if (!Schema::hasColumn('group_members', 'group_members_group_id_foreign')) {
                    $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
                }
                if (!Schema::hasColumn('group_members', 'group_members_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove foreign key constraints
        if (Schema::hasTable('groups')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
            });
        }

        if (Schema::hasTable('group_members')) {
            Schema::table('group_members', function (Blueprint $table) {
                $table->dropForeign(['group_id']);
                $table->dropForeign(['user_id']);
            });
        }
    }
}
