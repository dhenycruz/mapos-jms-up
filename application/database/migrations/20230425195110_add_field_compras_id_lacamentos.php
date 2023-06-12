<?php

class Migration_add_field_compras_id_lacamentos extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('lancamentos', [
            'compras_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('lancamentos', 'compras_id');
    }
}

// _add_field_compras_id_lacamentos