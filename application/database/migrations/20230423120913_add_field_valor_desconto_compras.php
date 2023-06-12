<?php

class Migration_add_field_valor_desconto_compras extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `compras` CHANGE `valorTotal` `valorTotal` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `compras` CHANGE `desconto` `desconto` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `compras` ADD `valor_desconto` DECIMAL(10, 2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `itens_de_compras` CHANGE `subTotal` `subTotal` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `itens_de_compras` CHANGE `preco` `preco` DECIMAL(10,2) NULL DEFAULT 0");

        // adicionando field tipo_desconto
        $this->db->query("ALTER TABLE `compras` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL");
    }

    public function down()
    {
        
        $this->db->query("ALTER TABLE `vendas` DROP `desconto`");
        $this->db->query("ALTER TABLE `vendas` DROP `valor_desconto`");
        $this->db->query("ALTER TABLE `compras` DROP `tipo_desconto`");
    }
}