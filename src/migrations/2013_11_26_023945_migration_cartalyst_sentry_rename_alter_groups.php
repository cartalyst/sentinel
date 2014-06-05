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

class MigrationCartalystSentinelRenameAlterGroups extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('groups', function(Blueprint $table)
		{
			$table->string('slug')->after('id')->default('');
			$table->dropUnique('groups_name_unique');
		});

		$groups = DB::table('groups')->get();

		foreach ($groups as $group)
		{
			$permissions = [];

			if ($group->permissions)
			{
				foreach (json_decode($group->permissions) as $key => $value)
				{
					$permissions[$key] = (bool) $value;
				}
			}

			DB::table('groups')
				->where('id', $group->id)
				->update([
					'slug' => Str::slug($group->name),
					'permissions' => (count($permissions) > 0) ? json_encode($permissions) : '',
				]);
		}

		Schema::table('groups', function(Blueprint $table)
		{
			$table->unique('slug');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('groups', function(Blueprint $table)
		{
			$table->dropUnique('groups_slug_unique');
			$table->unique('name');
		});

		Schema::table('groups', function(Blueprint $table)
		{
			$table->dropColumn('slug');
		});

		$groups = DB::table('groups')->get();

		foreach ($groups as $group)
		{
			$permissions = [];

			if ($group->permissions)
			{
				foreach (json_decode($group->permissions) as $key => $value)
				{
					$permissions[$key] = (int) $value;
				}
			}

			DB::table('groups')
				->where('id', $group->id)
				->update([
					'permissions' => (count($permissions) > 0) ? json_encode($permissions) : '',
				]);
		}
	}

}
