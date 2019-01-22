<?php

use Phinx\Migration\AbstractMigration;

class TodoTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('todolists');
        $table->addColumn('user_id','integer')
            ->addColumn('name', 'string')
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addForeignKey('user_id', 'users', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->create();

        $table = $this->table('tasks');
        $table->addColumn('todolist_id','integer')
            ->addColumn('title', 'string')
            ->addColumn('user_id','integer')
            ->addColumn('description', 'text')
            ->addColumn('done', 'boolean')
            ->addColumn('status', 'string')
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('concluded_at', 'datetime', ['null' => true])
            ->addForeignKey('todolist_id', 'todolists', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->addForeignKey('user_id', 'users', 'id', array('delete' => 'CASCADE', 'update' => 'CASCADE'))
            ->create();

    }
}
