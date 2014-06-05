<?php
/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Sentinel
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrationCartalystSentinelAlterUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('persist_code');
		});

		Schema::table('users', function(Blueprint $table)
		{
			$table->text('persistence_codes')->after('password')->nullable();
		});

		$users = DB::table('users')->get();

		foreach ($users as $user)
		{
			$permissions = [];

			if ($user->permissions)
			{
				foreach (json_decode($user->permissions) as $key => $value)
				{
					$permissions[$key] = ($value == 1);
				}
			}

			DB::table('users')
				->update([
					'permissions' => (count($permissions) > 0) ? json_encode($permissions) : '',
				]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('persistence_codes');
		});

		Schema::table('users', function(Blueprint $table)
		{
			$table->string('persist_code')->nullable();
		});

		$users = DB::table('users')->get();

		foreach ($users as $user)
		{
			$permissions = [];

			if ($user->permissions)
			{
				foreach (json_decode($user->permissions) as $key => $value)
				{
					$permissions[$key] = ($value == true) ? 1 : -1;
				}
			}

			DB::table('users')
				->update([
					'permissions' => (count($permissions) > 0) ? json_encode($permissions) : '',
				]);
		}
	}

}
