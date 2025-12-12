<?php

namespace app\controller;

use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;

class Cliente extends Base
{

    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Lista de cliente'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('listcliente'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Cadastro de cliente'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('cliente'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function insert($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $nome_fantasia = $form['nome_fantasia'];
            $sobrenome_razaosocial = $form['sobrenome_razaosocial'];
            $cpf_cnpj = $form['cpf_cnpj'];
            $rg_ie = $form['rg_ie'];
            $celular = $form['celular'];

            $FieldsAndValues = [
                'nome_fantasia' => $nome_fantasia,
                'sobrenome_razaosocial' => $sobrenome_razaosocial,
                'cpf_cnpj' => $cpf_cnpj,
                'rg_ie' => $rg_ie,
                'celular' => $celular,
            ];
            $IsSave = InsertQuery::table('cliente')->save($FieldsAndValues);
            if (!$IsSave) {
                echo json_encode(['status' => false, 'msg' => 'Erro ao salvar', 'id' => 0]);
                die;
            }

            echo json_encode(['status' => true, 'msg' => 'Salvo com sucesso!', 'id' => 0]);
            die;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function listcliente($request, $response)
    {        
        #Captura todas a variaveis de forma mais segura VARIAVEIS POST.
        $form = $request->getParsedBody();
        #Qual a coluna da tabela deve ser ordenada.
        $order = $form['order'][0]['column'];
        #Tipo de ordenação
        $orderType = $form['order'][0]['dir'];
        #Em qual registro se inicia o retorno dos registro, OFFSET
        $start = $form['start'];
        #Limite de registro a serem retornados do banco de dados LIMIT
        $length = $form['length'];
        $fields = [
            0 => 'id',
            1 => 'nome_fantasia',
            2 => 'sobrenome_razaosocial',
            3 => 'cpf_cnpj',
            4 => 'rg_ie',
            5 => 'celular',
        ];
        #Capturamos o nome do capo a ser ordenado.
        $orderField = $fields[$order];
        #O termo pesquisado
        $term = $form['search']['value'];
        $query = SelectQuery::select('id,nome_fantasia,sobrenome_razaosocial,cpf_cnpj,rg_ie,celular')->from('cliente');
        if (!is_null($term) && ($term !== '')) {
            $query->where('cliente.nome_fantasia', 'ilike', "%{$term}%", 'or')
                ->where('cliente.sobrenome_razaosocial', 'ilike', "%{$term}%", 'or')
                ->where('cliente.cpf_cnpj', 'ilike', "%{$term}%", 'or')
                ->where('cliente.rg_ie', 'ilike', "%{$term}%", 'or')
                ->where('cliente.celular', 'ilike', "%{$term}%");
        }
        $users = $query
            ->order($orderField, $orderType)
            ->limit($length, $start)
            ->fetchAll();
        $userData = [];
        foreach ($users as $key => $value) {
            $userData[$key] = [
                $value['id'],
                $value['nome_fantasia'],
                $value['sobrenome_razaosocial'],
                $value['cpf_cnpj'],
                $value['rg_ie'],
                $value['celular'],
                "<button class='btn btn-warning'>Editar</button>
                <button class='btn btn-danger'>Excluir</button>"
            ];
        }
        $data = [
            'status' => true,
            'recordsTotal' => count($users),
            'recordsFiltered' => count($users),
            'data' => $userData
        ];
        $payload = json_encode($data);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}