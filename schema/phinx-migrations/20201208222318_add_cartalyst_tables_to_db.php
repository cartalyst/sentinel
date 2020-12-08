<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCartalystTablesToDb extends AbstractMigration
{
    public function up()
    {
        // place up commands here
        /**
        # Dump of table activations
        # ------------------------------------------------------------

            CREATE TABLE `activations` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(10) unsigned NOT NULL,
              `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `completed` tinyint(1) NOT NULL DEFAULT '0',
              `completed_at` timestamp NULL DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        */
        if( !$this->hasTable('activations') ) {

            $table = $this->table('activations');
            $table//->addColumn('id', 'integer', ['signed' => false, 'identity' => true]) // automatically generated
                  ->addColumn('user_id', 'integer', ['signed' => false, 'null' => false])
                  ->addColumn('code', 'string', ['limit' => 255, 'null' => false]);
                    
            if($this->currentAdapterIs('mysql')) {
            
                $table->addColumn('completed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'null' => false, 'default' => 0]);
                
            } else {
                
                $table->addColumn('completed', 'integer', ['null' => false, 'default' => 0]);
            }
            
            $table->addColumn('completed_at', 'timestamp',  ['null' => true])
                  ->addColumn('created_at', 'timestamp',    ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp',    ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->create();
            
        } // if( !$this->hasTable('activations') )
        
        /**
        # Dump of table persistences
        # ------------------------------------------------------------

            CREATE TABLE `persistences` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(10) unsigned NOT NULL,
                `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `persistences_code_unique` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        */
        if( !$this->hasTable('persistences') ) {
            
            $table = $this->table('persistences');
            $table//->addColumn('id', 'integer', ['signed'=>false, 'identity'=>true]) // automatically generated
                  ->addColumn('user_id', 'integer', ['signed'=>false, 'null'=>false])
                  ->addColumn('code', 'string', ['limit'=>255, 'null'=>false])
                  ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addIndex(['code'], ['name' => 'persistences_code_unique', 'unique' => true])
                  ->create();
        }
        
        /**
        # Dump of table reminders
        # ------------------------------------------------------------

            CREATE TABLE `reminders` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(10) unsigned NOT NULL,
                `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `completed` tinyint(1) NOT NULL DEFAULT '0',
                `completed_at` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        */
        if( !$this->hasTable('reminders') ) {
            
            $table = $this->table('reminders');
            $table//->addColumn('id', 'integer', ['signed' => false, 'identity' => true]) // automatically generated
                  ->addColumn('user_id', 'integer', ['signed' => false, 'null' => false])
                  ->addColumn('code', 'string', ['limit' => 255, 'null' => false]);
                    
            if($this->currentAdapterIs('mysql')) {
            
                $table->addColumn('completed', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'null' => false, 'default' => 0]);
                
            } else {
                
                $table->addColumn('completed', 'integer', ['null' => false, 'default' => 0]);
            }
            
            $table->addColumn('completed_at', 'timestamp',  ['null' => true])
                  ->addColumn('created_at', 'timestamp',    ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp',    ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->create();
        }
        
        /**
        # Dump of table roles
        # ------------------------------------------------------------

            CREATE TABLE `roles` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `permissions` text COLLATE utf8_unicode_ci,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `roles_slug_unique` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; 
        */
        if( !$this->hasTable('roles') ) {
            
            $table = $this->table('roles');
            $table//->addColumn('id', 'integer', ['signed'=>false, 'identity'=>true]) // automatically generated
                  ->addColumn('slug', 'string', ['limit'=>255, 'null'=>false])
                  ->addColumn('name', 'string', ['limit'=>255, 'null'=>false])
                  ->addColumn('permissions', 'text', ['null'=>true])
                  ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addIndex(['slug'], ['name' => 'roles_slug_unique', 'unique' => true])
                  ->create();
        }
        
        /**
        # Dump of table role_users
        # ------------------------------------------------------------

            CREATE TABLE `role_users` (
                `user_id` int(10) unsigned NOT NULL,
                `role_id` int(10) unsigned NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`user_id`,`role_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        */
        if( !$this->hasTable('role_users') ) {
            
            $table = $this->table('role_users');
            $table//->addColumn('id', 'integer', ['signed'=>false, 'identity'=>true]) // automatically generated
                  ->addColumn('user_id', 'integer', ['signed'=>false, 'null'=>false])
                  ->addColumn('role_id', 'integer', ['signed'=>false, 'null'=>false])
                  ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->create();
        }
        
        /**
        # Dump of table throttle
        # ------------------------------------------------------------

            CREATE TABLE `throttle` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(10) unsigned DEFAULT NULL,
                `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `throttle_user_id_index` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        */
        if( !$this->hasTable('throttle') ) {
            
            $table = $this->table('throttle');
            $table//->addColumn('id', 'integer', ['signed'=>false, 'identity'=>true]) // automatically generated
                  ->addColumn('user_id', 'integer', ['signed'=>false, 'null'=>false])
                  ->addColumn('type', 'string', ['limit'=>255, 'null'=>false])
                  ->addColumn('ip', 'string', ['limit'=>255, 'null'=>true])
                  ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addIndex(['user_id'], ['name' => 'throttle_user_id_index'])
                  ->create();
        }
        
        /**
        # Dump of table users
        # ------------------------------------------------------------

            CREATE TABLE `users` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `permissions` text COLLATE utf8_unicode_ci,
                `last_login` timestamp NULL DEFAULT NULL,
                `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `users_email_unique` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        */
        if( !$this->hasTable('users') ) {
            
            $table = $this->table('users');
            $table//->addColumn('id', 'integer', ['signed'=>false, 'identity'=>true]) // automatically generated
                  ->addColumn('email', 'string', ['limit'=>255, 'null'=>false])
                  ->addColumn('password', 'string', ['limit'=>255, 'null'=>false])
                  ->addColumn('permissions', 'text', ['null'=>true])
                  ->addColumn('last_login', 'timestamp', ['null' => true])
                  ->addColumn('first_name', 'string', ['limit'=>255, 'null'=>true])
                  ->addColumn('last_name', 'string', ['limit'=>255, 'null'=>true])
                  ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addColumn('updated_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                  ->addIndex(['email'], ['name' => 'users_email_unique', 'unique' => true])
                  ->create();
        }
    }
    
    public function down()
    {
        // place down commands here
        $this->hasTable('activations') && $this->table('activations')->drop()->save();
        $this->hasTable('persistences') && $this->table('persistences')->drop()->save();
        $this->hasTable('reminders') && $this->table('reminders')->drop()->save();
        $this->hasTable('roles') && $this->table('roles')->drop()->save();
        $this->hasTable('role_users') && $this->table('role_users')->drop()->save();
        $this->hasTable('throttle') && $this->table('throttle')->drop()->save();
        $this->hasTable('users') && $this->table('users')->drop()->save();

    }
    
    protected function currentAdapterIs($adapter='mysql') {
        
        return ( 
            trim(strtolower($this->getAdapter()->getAdapterType())) 
            === trim(strtolower($adapter)) 
        );
    }
}
