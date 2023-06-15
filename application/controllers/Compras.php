<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Compras extends MY_Controller
{
    /**
     * author: Dheniarley Cruz
     * email: dheniarley.ds@gmail.com
     *
     */

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('form');
        $this->load->model('compras_model');
        $this->data['menuCompras'] = 'Compras';
    }

    public function index()
    {
        $this->gerenciar();
    }

    public function gerenciar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar vendas.');
            redirect(base_url());
        }

        $this->load->library('pagination');


        $this->data['configuration']['base_url'] = site_url('compras/gerenciar/');
        // $this->data['configuration']['total_rows'] = $this->vendas_model->count('vendas');

        $this->pagination->initialize($this->data['configuration']);

        $this->data['results'] = $this->compras_model->get('compras', '*', '', $this->data['configuration']['per_page'], $this->uri->segment(3));

        $this->data['view'] = 'compras/compras';
        return $this->layout();
    }

    public function adicionar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'aCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para adicionar Compras.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('compras') === false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {
            $dataCompra = $this->input->post('dataCompra');
            
            try {
                $dataCompra = explode('/', $dataCompra);
                $dataCompra = $dataCompra[2] . '-' . $dataCompra[1] . '-' . $dataCompra[0];
            } catch (Exception $e) {
                $dataCompra = date('Y/m/d');
            }

            $data = [
                'dataCompra' => $dataCompra,
                'observacoes' => $this->input->post('observacoes'),
                'observacoes_cliente' => $this->input->post('observacoes_cliente'),
                'clientes_id' => $this->input->post('clientes_id'),
                'usuarios_id' => $this->input->post('usuarios_id'),
                'faturado' => 0,
            ];

            if (is_numeric($id = $this->compras_model->add('compras', $data,  true))) {
                $this->session->set_flashdata('success', 'Compra iniciada com sucesso, adicione os produtos.');
                log_info('Adicionou uma compra.');
                redirect(site_url('compras/editar/') . $id);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['view'] = 'compras/adicionarCompra';
        return $this->layout();
    }

    public function editar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            log_message('error', 'Testando');
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar compras');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('compras') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' :  false);
        } else {
            $dataCompra = $this->input->post('dataCompra');

            try {
                $dataCompra = explode('/', $dataCompra);
                $dataCompra = $dataCompra[2] . '-' . $dataCompra[1] . '-' . $dataCompra[0];
            } catch (Exception $e) {
                $dataCompra = date('Y/m/d');
            }

            $data = [
                'dataCompra' => $dataCompra,
                'observacoes' => $this->input->post('observacoes'),
                'observacoes_cliente' => $this->input->post('observacoes_cliente'),
                'usuarios_id' => $this->input->post('usuarios_id'),
                'clientes_id' => $this->input->post('clientes_id'),
            ];

            if ($this->compras_model->edit('compras', $data, 'idCompras', $this->input->post('idCompras')) == true) {
                $this->session->set_flashdata('success', 'Compra editada com sucesso!');
                log_infO('Alterou uma compra ID:' . $this->input->post('idCompras'));
                redirect(site_url('compras/editar/') . $this->input->post('idCompras'));
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $this->data['result'] = $this->compras_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->compras_model->getProdutos($this->uri->segment(3));
        $this->data['view'] = 'compras/editarCompra';

        return $this->layout();
    }

    public function excluir()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'dCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para excluir compras.');
        }

        $id = $this->input->post('id');

        $editavel = $this->compras_model->isEditable($id);

        if (!$editavel) {
            $this->session->set_flashdata('error', 'Erro ao tentar excluir. Compra já faturada!');
            redirect(site_url('compras/gerenciar'));
        }

        $compra = $this->compras_model->getById($id);

        if ((int) $compra->faturado === 1) {
            $this->compras_model->delete('lancamentos', 'descricao', "Fatura de compra - #${id}");
        }

        $this->compras_model->delete('itens_de_compras', 'compras_id', $id);
        $this->compras_model->delete('compras', 'idCompras', $id);

        log_info('Removeu uma compra. ID: ' . $id);

        $this->session->set_flashdata('success', 'Compra excluída com sucesso!');
        redirect(site_url('compras/gerenciar/'));
    }

    public function adicionarProduto()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar compras.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('quantidade', 'Quantidade', 'trim|required');
        $this->form_validation->set_rules('idProduto', 'Produto', 'trim|required');
        $this->form_validation->set_rules('idComprasProduto', 'Compras', 'trim|required');

        //compra editavel
        $editavel  = $this->compras_model->isEditable($this->input->post('idComprasProduto'));

        if (!$editavel) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'messages' => 'Compra já faturada']));
        }

        if ($this->form_validation->run() == false) {
            echo json_encode(['result' => false, 'messages' => 'Produto não encontrado no estoque, cadastre primeiro o produto.']);
        } else {
            $compra_id = $this->input->post('compras_id');
            $preco = $this->input->post('preco');
            $quantidade = $this->input->post('quantidade');
            $subtotal = $preco * $quantidade;
            $produto = $this->input->post('idProduto');

            $data = [
                'quantidade' => $quantidade,
                'subtotal' => $subtotal,
                'produtos_id' => $produto,
                'preco' => $preco,
                'compras_id' => $this->input->post('idComprasProduto'),
            ];

            $compra = $this->compras_model->getById($compra_id);

            if ($this->compras_model->add('itens_de_compras', $data) == true) {
                $this->load->model('produtos_model');

                /* if ($this->data['configuration']['control_estoque']) {
                    $this->produtos_model->updateEstoque($produto, $quantidade, '+');
                } */

                $this->db->set('desconto', 0.00);
                $this->db->set('valor_desconto', 0.00);
                $this->db->set('tipo_desconto', null);
                $this->db->where('idCompras', $this->input->post('idComprasProduto'));
                $this->db->update('compras');

                log_info('Adicionou produto a uma compra.');

                echo json_encode(['result' => true]);
            } else {
                echo json_encode(['result' => false]);
            }
        }
    }

    public function excluirProduto()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar Compras.');
            redirect(base_url());
        }

        $editavel = $this->compras_model->isEditable($this->input->post('idCompras'));

        if (!$editavel) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['result' => false, 'messages' => '<br /><br /> <strong>Motivo:</strong> Venda já faturada']));
        }

        $ID = $this->input->post('idProduto');

        if ($this->compras_model->delete('itens_de_compras', 'idItens', $ID) == true) {
            /* $quantidade = $this->input->post('quantidade');
            $produto = $this->input->post('produto');

            $this->load->model('produtos_model');

            if ($this->data['configuration']['control_estoque']) {
                $this->produtos_model->updateEstoque($produto, $quantidade, '-');
            } */

            log_info('Removeu produto de uma compra.');
            echo json_encode(['result' => true]);
        } else {
            echo json_encode(['result' => false]);
        }
    }

    public function adicionarDesconto()
    {
        if ($this->input->post('desconto') == "") {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['messages' => 'Campo desconto vazio']));
        } else {
            $idCompras = $this->input->post('idCompras');
            $data = [
                'desconto' => $this->input->post('desconto'),
                'tipo_desconto' => $this->input->post('tipoDesconto'),
                'valor_desconto' => $this->input->post('resultado')
            ];

            $editavel = $this->compras_model->isEditable($idCompras);

            if (!$editavel) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'messages' => 'Desconto não pode ser adicionado. Compra já faturada/cancelada']));
            }

            if ($this->compras_model->edit('compras', $data, 'idCompras', $idCompras) == true) {
                log_info('Adicionou um desconto na Compra. ID: ' . $idCompras);

                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(201)
                    ->set_output(json_encode(['result' => true, 'messages' => 'Desconto adicionado com sucesso!'])); 
            } else {
                log_info('Ocorreu um erro ao tentar adiciona  desconto a compra: '. $idCompras);
                
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode(['result' => false, 'messages' => 'Ocorreu um erro ao tentar adicionar desconto a compra.']));
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['result' => false, 'messages' => 'Ocorreu um erro ao tentar adicionar desconto a compra.']));
    }

    public function faturar()
    {
        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'eCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para editar Compras.');
            redirect(base_url());
        }

        $this->load->library('form_validation');
        $this->data['custom_error'] = '';

        if ($this->form_validation->run('receita') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">'. validatioin_erros() . '</div>' : false);
        } else {
            $compra_id = $this->input->post('compras_id');
            $vencimento = $this->input->post('vencimento');
            $recebimento = $this->input->post('recebimento');

            try {
                $vencimento = explode('/', $vencimento);
                $vencimento = $vencimento[2] . '-' . $vencimento[1] . '-' . $vencimento[0];

                if ($recebimento != null) {
                    $recebimento = explode('/', $recebimento);
                    $recebimento = $recebimento[2] . '-' . $recebimento[1] . '-' . $recebimento[0];
                }
            } catch (Exception $e) {
                $vencimento = date('Y/m/d');
            }
            
            $compras = $this->compras_model->getById($compra_id);
            $data = [
                'compras_id' => $compra_id,
                'descricao' => set_value('descricao'),
                'valor' => $this->input->post('valor'),
                'desconto' => $compras->desconto,
                'tipo_desconto' => $compras->tipo_desconto,
                'valor_desconto' => $compras->valor_desconto,
                'clientes_id' => $this->input->post('clientes_id'),
                'data_vencimento' => $vencimento,
                'data_pagamento' => $recebimento,
                'baixado' => $this->input->post('recebido') == 1 ? true : false,
                'cliente_fornecedor' => set_value('cliente'),
                'forma_pgto' => $this->input->post('formaPgto'),
                'tipo' => $this->input->post('tipo'),
                'usuarios_id' => $this->session->userdata('id_admin'),
            ];

            if ($this->compras_model->add('lancamentos', $data) == true)
            {
                $compra = $this->input->post('compras_id');
                $this->db->set('faturado', 1);
                $this->db->set('valorTotal', $this->input->post('valor'));
                $this->db->where('idCompras', $compra);
                $this->db->update('compras');

                $this->load->model('produtos_model');
                if ($this->data['configuration']['control_estoque']) {
                    $produtos = $this->compras_model->getProdutos($compra_id);

                    foreach($produtos as $produto) {
                        $this->produtos_model->updateEstoque($produto->produtos_id, $produto->quantidade, '+');
                    }
                }

                log_info('Faturou uma compra');
                $this->session->set_flashdata('success', 'Compra faturada com sucesso!');
                $json = ['result' => true];
                echo json_encode($json);
                die();
            } else {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar compra.');

                $json = ['result' => false];
                echo json_encode($json);
                die();
            }

            $this->session->set_flashdata('error', 'Ocorreu um erro ao tentar faturar compra.');
            $json = ['result' => false];
            echo json_encode($json);
        }
    }
    public function autoCompleteProduto()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->compras_model->autoCompleteProduto($q);
        }
    }

    public function autoCompleteCliente()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->compras_model->autoCompleteCliente($q);
        }
    }

    public function autoCompleteUsuario()
    {
        if (isset($_GET['term'])) {
            $q = strtolower($_GET['term']);
            $this->compras_model->autoCompleteUsuario($q);
        }
    }

    public function visualizar()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCompra')) {
            $this->session->set_flashdata('error', 'Você  não tem permissão para visualizar compras.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->compras_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->compras_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();
        $this->data['modalGerarPagamento'] = $this->load->view(
            'cobrancas/modalGerarPagamento',
            [
                'id' => $this->uri->segment(3),
                'tipo' => 'compra',
            ],
            true
        );

        $this->data['view'] = 'compras/visualizarCompra';
    
        return $this->layout();
    }

    public function imprimir()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCompra')) {
            $this->session->set_flashdata('errror', 'Você não tem permissão para visualizar compras.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->compras_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->compras_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->view('compras/imprimirCompra', $this->data);
    }

    public function imprimirTermica()
    {
        if (!$this->uri->segment(3) || !is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (!$this->permission->checkPermission($this->session->userdata('permissao'), 'vCompra')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para visualizar compras.');
            redirect(base_url());
        }

        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->data['result'] = $this->compras_model->getById($this->uri->segment(3));
        $this->data['produtos'] = $this->compras_model->getProdutos($this->uri->segment(3));
        $this->data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->view('compras/imprimirCompraTermica', $this->data);
    }
}