<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthorsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 191, 'unique' => true, 'null' => false],
            'date_of_birth' => ['type' => 'DATE', 'null' => false]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('authors');
    }

    public function down()
    {
        $this->forge->dropTable('authors');
    }
}