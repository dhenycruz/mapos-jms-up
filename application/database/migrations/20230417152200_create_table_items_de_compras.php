<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_table_items_de_compras extends CI_Migration
{
    public function up()
    {
        ## Create Table itens_de_compras
        $this->dbforge->add_field([
            'idItens' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'subTotal' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'quantidade' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'preco' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'compras_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'produtos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
        ]);
        $this->dbforge->add_key("idItens", true);
        $this->dbforge->create_table("itens_de_compras", true);
        $this->db->query('ALTER TABLE  `itens_de_compras` ADD INDEX `fk_itens_de_compras_compras1` (`compras_id` ASC)');
        $this->db->query('ALTER TABLE  `itens_de_compras` ADD INDEX `fk_itens_de_compras_produtos1` (`produtos_id` ASC)');
        $this->db->query('ALTER TABLE  `itens_de_compras` ADD CONSTRAINT `fk_itens_de_compras_compras1`
			FOREIGN KEY (`compras_id`)
			REFERENCES `compras` (`idCompras`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `itens_de_compras` ADD CONSTRAINT `fk_itens_de_compras_produtos1`
			FOREIGN KEY (`produtos_id`)
			REFERENCES `produtos` (`idProdutos`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE  `itens_de_compras` ENGINE = InnoDB');
    }
    public function down()
    {
        ### Drop table compras ##
        $this->dbforge->drop_table("compras", true);
    }
}