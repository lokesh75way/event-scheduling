<?php


use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $initial_data = [
            [
                'name'    => 'Jayson',
                'created' => date('Y-m-d H:i:s'),
                'modified'=> date('Y-m-d H:i:s')
            ],
            [
                'name'    => 'Irene',
                'created' => date('Y-m-d H:i:s'),
                'modified'=> date('Y-m-d H:i:s')
            ],
            [
                'name'    => 'Steve',
                'created' => date('Y-m-d H:i:s'),
                'modified'=> date('Y-m-d H:i:s')
            ],
            [
                'name'    => 'Lokesh',
                'created' => date('Y-m-d H:i:s'),
                'modified'=> date('Y-m-d H:i:s')
            ],
            [
                'name'    => 'Avinash',
                'created' => date('Y-m-d H:i:s'),
                'modified'=> date('Y-m-d H:i:s')
            ]
        ];

        $users = $this->table('users');
        $users->insert($initial_data)
              ->save();

        // empty the table
        // $users->truncate();
        
    }
}
