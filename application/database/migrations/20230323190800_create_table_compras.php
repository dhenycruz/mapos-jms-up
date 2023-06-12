<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_table_compras extends CI_Migration
{
    public function up()
    {
        ## Create Table Compras
        $this->dbforge->add_field([
            'idCompras' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'dataCompra' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'valorTotal' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'desconto' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'faturado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'lancamentos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idCompras", true);
        $this->dbforge->create_table("compras", true);
        $this->db->query('ALTER TABLE  `compras` ADD INDEX `fk_compras_clientes1` (`clientes_id` ASC)');
        $this->db->query('ALTER TABLE  `compras` ADD INDEX `fk_compras_usuarios1` (`usuarios_id` ASC)');
        $this->db->query('ALTER TABLE  `compras` ADD INDEX `fk_compras_lancamentos1` (`lancamentos_id` ASC)');
        $this->db->query('ALTER TABLE  `compras` ADD CONSTRAINT `fk_compras_clientes1`
			FOREIGN KEY (`clientes_id`)
			REFERENCES `clientes` (`idClientes`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `compras` ADD CONSTRAINT `fk_compras_usuarios1`
			FOREIGN KEY (`usuarios_id`)
			REFERENCES `usuarios` (`idUsuarios`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `compras` ADD CONSTRAINT `fk_compras_lancamentos1`
			FOREIGN KEY (`lancamentos_id`)
			REFERENCES `lancamentos` (`idLancamentos`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `compras` ENGINE = InnoDB');
    }

    public function down()
    {
        ### Drop table vendas ##
        $this->dbforge->drop_table("compras", true);
    }
}
