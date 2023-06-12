<?php

class Migration_add_observacoes_to_compras extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('compras', [
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->dbforge->add_column('compras', [
            'observacoes_cliente' => [
                'type' => 'TEXT',
                'null' => true,
                'default' => null,
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('compras', 'observacoes');
        $this->dbforge->drop_column('compras', 'observacoes_cliente');
    }
}
