<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'first_name', 255)->nullable()->change();
        });
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 255)->after('first_name')->nullable();
            }
            $table->string('email')->nullable()->change();
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone',20)->after('email')->nullable();
            }
            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo',50)->after('password')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('first_name', 'name');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_name');
            $table->dropColumn('phone');
            $table->dropColumn('profile_photo');
        });
    }
}
