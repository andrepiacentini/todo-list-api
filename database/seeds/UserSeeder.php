<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Creates default user structure
     */
    public function run()
    {
        $this->getAdapter()->beginTransaction();

        try {
            $this->getAdapter()->execute('SET FOREIGN_KEY_CHECKS = 0');

            $user_data = array(
                array(
                    'name'    => 'teste',
                    'username'    => 'teste@andrepiacentini.com.br',
                    'password' => '$2y$10$fV4DQhP6o6Oiw/c4IWSr4eg35kwVbVNYmyGoPEhbhYCFZ4SFDWrqi',//secret123
                    'phone' => '048988776655',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'language' => 'pt-br'
                )
            );

            $users = $this->table('users');
            $users->truncate();
            $users->insert($user_data)
                  ->save();

            $area_permission_data = array(
                array(
                    'user_id'    => 1,
                    'area'    => 'administrador',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                array(
                    'user_id'    => 1,
                    'area'    => 'usuario',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                )
            );

            $area_permission = $this->table('area_permission');
            $area_permission->truncate();
            $area_permission->insert($area_permission_data)
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
