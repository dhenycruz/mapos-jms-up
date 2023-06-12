<?php

use Piggly\Pix\StaticPayload;

if (! defined('BASEPATH')) {
    exit('No direct script acess allowrd');
}

class Compras_model extends CI_Model
{
    /**
     * author: Dheniarley Cruz
     * email: dheniarley.ds@gmail.com
     * message: Criando uma nova feature - Compras
     */

     public function __construct()
     {
        parent::__construct();
     }

     public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
     {
        $this->db->select($fields.', clientes.nomeCliente, clientes.idClientes');
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        $this->db->join('clientes', 'clientes.idClientes = '.$table.'.clientes_id');
        $this->db->order_by('idCompras', 'desc');
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

       $result = !$one ? $query->result() : $query->row();

       return $result;
     }

    public function getById($id)
    {
        $this->db->select('compras.*, clientes.*, clientes.email as emailCliente, usuarios.telefone as telefone_usuario, usuarios.email as email_usuario, usuarios.nome');
        $this->db->from('compras');
        $this->db->join('clientes', 'clientes.idClientes = compras.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = compras.usuarios_id');
        $this->db->where('compras.idCompras', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function add($table, $data, $returnId = false)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            if ($returnId == true) {
                return $this->db->insert_id($table);
            }
            return true;
        }

        return false;
    }

    public function isEditable($id = null)
    {
        if ($compras = $this->getById($id)) {
            if ($compras->faturado) {
                return false;
            }
        }
        return true;
    }
    
    public function edit($table, $data, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }

        return false;
    }

    public function delete($table, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table)
    {
        return $this->db->count_all($table);
    }

    public function getProdutos($id = null)
    {
        $this->db->select('itens_de_compras.*, produtos.*');
        $this->db->from('itens_de_compras');
        $this->db->join('produtos', 'produtos.idProdutos = itens_de_compras.produtos_id');
        $this->db->where('compras_id', $id);
        
        return $this->db->get()->result();
    }

    public function autoCompleteProduto($q)
    {
        $this->db->select('*');
        $this->db->limit(5);
        $this->db->like('descricao', $q);
        $query = $this->db->get('produtos');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label'=>$row['descricao'].' | Preço: R$ '.$row['precoCompra'].' | Estoque: '.$row['estoque'],'estoque'=>$row['estoque'],'id'=>$row['idProdutos'],'preco'=>$row['precoCompra']];
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteCliente($q)
    {
        $this->db->select('*');
        $this->db->limit(5);
        $this->db->like('nomeCliente', $q);
        $query = $this->db->get('clientes');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label'=>$row['nomeCliente'].' | Telefone: '.$row['telefone'],'id'=>$row['idClientes']];
            }
            echo json_encode($row_set);
        } else {
            $row_set[] = ['label'=> 'Adicionar cliente...', 'id' => null];
            echo json_encode($row_set);
        }
    }

    public function autoCompleteUsuario($q)
    {
        $this->db->select('*');
        $this->db->limit(5);
        $this->db->like('nome', $q);
        $this->db->where('situacao', 1);
        $query = $this->db->get('usuarios');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label'=>$row['nome'].' | Telefone: '.$row['telefone'],'id'=>$row['idUsuarios']];
            }
            echo json_encode($row_set);
        }
    }
}

/* End of file Compras_model.php */
/* Location: ./application/models/Compras_model.php */