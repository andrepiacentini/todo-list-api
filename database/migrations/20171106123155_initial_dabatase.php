<?php

use Phinx\Migration\AbstractMigration;

class InitialDabatase extends AbstractMigration
{
    /**
     *
     */
    public function change()
    {
        $session_table = $this->table('session', ['id'=>false,'primary_key'=>null]);
        $session_table->addColumn('id','char',['limit'=>32])
            ->addColumn('name','char',['limit'=>32])
            ->addColumn('modified','integer',['limit'=>11, 'null'=>true, 'default'=>null])
            ->addColumn('lifetime','integer',['limit'=>11, 'null'=>true, 'default'=>null])
            ->addColumn('data','text',['null'=>true])
            ->create();

        $users_table = $this->table('users');
        $users_table->addColumn('name', 'string')
            ->addColumn('username', 'string', array('limit' => 255, 'null' => false))
            ->addColumn('password', 'string')
            ->addColumn('remember_token', 'string')
            ->addColumn('nickname', 'string')
            ->addColumn('image', 'string')
            ->addColumn('social_networks', 'string')
            ->addColumn('is_active', 'integer')
            ->addColumn('phone', 'string')
            ->addColumn('language', 'string')
            ->addColumn('created_at', 'datetime', array('null' => true))
            ->addColumn('updated_at', 'datetime', array('null' => true))
            ->addColumn('deleted_at', 'datetime', array('null' => true))
            ->create();

        $users_table->changeColumn('nickname', 'string',  array('limit' => 255, 'null' => true))
            ->changeColumn('image', 'string', array('limit' => 255, 'null' => true))
            ->changeColumn('social_networks', 'string', array('limit' => 255, 'null' => true))
            ->changeColumn('phone', 'string', array('limit' => 255))
            ->changeColumn('remember_token', 'string', array('limit' => 255, 'null' => true))
            ->changeColumn('is_active', 'integer', array('limit' => 1, 'default' => 1))
            ->addColumn('last_token', 'binary', array('null' => true))
            ->update();

        $users_table = $this->table('users');
        $users_table->addColumn('last_login', 'datetime', array('null' => true))
                    ->addIndex('username', array('unique' => true))
            ->update();



        $user_emails_table = $this->table('user_emails');
        $user_emails_table->addColumn('email', 'string')
            ->addColumn('user_id', 'integer')
            ->addForeignKey('user_id', 'users', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->save();

        $user_emails_table->addIndex('email', array('unique' => true))
            ->save();



        $blacklist = $this->table('login_blacklist');
        $blacklist->addColumn('token','text')
            ->addColumn('user_id','integer')
            ->addColumn('created_at', 'datetime', array('default' => 'CURRENT_TIMESTAMP'))
            ->addColumn('updated_at', 'datetime', array('null' => true))
            ->addColumn('active','boolean')
            ->addForeignKey('user_id', 'users', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->create();

        $blacklist = $this->table('login_blacklist');
        $blacklist->addColumn('ip', 'string', array("null" => true, "limit" => 50))
            ->update();



        $area_permission = $this->table('area_permission');
        $area_permission->addColumn('user_id','integer')
            ->addColumn('area','string')
            ->addColumn('created_at', 'datetime', array('default' => 'CURRENT_TIMESTAMP'))
            ->addColumn('updated_at', 'datetime', array('null' => true))
            ->create();

        $area_permission = $this->table('area_permission');
        $area_permission->addForeignKey('user_id', 'users', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->update();
    }
}
