<?php

use Phinx\Seed\AbstractSeed;

class SampleDataSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $this->getAdapter()->beginTransaction();

        try {
            $this->getAdapter()->execute('SET FOREIGN_KEY_CHECKS = 0');

            // Todo Lists
            $todolist_data = array(
                array(
                    'name' => 'Teste de Lista',
                    'user_id' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                )
            );

            $users = $this->table('todolists');
            $users->truncate();
            $users->insert($todolist_data)
                ->save();

            // Tasks
            $tasks_data = array(
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 1',
                    'description' => 'Descrição da tarefa 1',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 2',
                    'description' => 'Descrição da tarefa 2',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 5,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 3',
                    'description' => 'Descrição da tarefa 3',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 3,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 4',
                    'description' => 'Descrição da tarefa 4',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 5',
                    'description' => 'Descrição da tarefa 5',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 10,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 6',
                    'description' => 'Descrição da tarefa 6',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 8,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 7',
                    'description' => 'Descrição da tarefa 7',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 8',
                    'description' => 'Descrição da tarefa 8',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 2,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 9',
                    'description' => 'Descrição da tarefa 9',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 8,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 10',
                    'description' => 'Descrição da tarefa 10',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 10,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id' => 1,
                    'todolist_id' => 1,
                    'title' => 'Tarefa 11',
                    'description' => 'Descrição da tarefa 11',
                    'status' => 1,
                    'done' => 0,
                    'priority' => 3,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
            );

            $tasks = $this->table('tasks');
            $tasks->truncate();
            $tasks->insert($tasks_data)
                ->save();
        } catch (PDOException $e) {
            $this->getAdapter()->execute('SET FOREIGN_KEY_CHECKS = 1');
            $this->getAdapter()->rollbackTransaction();
            throw $e;
        }
        $this->getAdapter()->execute('SET FOREIGN_KEY_CHECKS = 1');
        $this->getAdapter()->commitTransaction();
    }
}
