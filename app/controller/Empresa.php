<?php

namespace app\controller;

use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;

class Empresa extends Base
{

    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Lista de Empresa'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('listempresa'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Cadastro de Empresa'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('empresa'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function insert($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $razao_social = $form['razao_social'];
            $fantasia = $form['fantasia'];
            $telefone = $form['telefone'];
            $cnpj = $form['cnpj'];
            $ie = $form['ie'];
            $cep = $form['cep'];
            $cidade = $form['cidade'];
            $estado = $form['estado'];

            $FieldsAndValues = [
                'razao_social' => $razao_social,
                'fantasia' => $fantasia,
                'telefone' => $telefone,
                'cnpj' => $cnpj,
                'ie' => $ie,
                'cep' => $cep,
                'cidade' => $cidade,
                'estado' => $estado,
            ];
            $IsSave = InsertQuery::table('empresa')->save($FieldsAndValues);
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

    public function listempresa($request, $response)
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
            1 => 'razao_social',
            2 => 'fantasia',
            3 => 'telefone',
            4 => 'cnpj',
            5 => 'ie',
            7 => 'cep',
            6 => 'cidade',
            7 => 'estado',
        ];
        #Capturamos o nome do capo a ser ordenado.
        $orderField = $fields[$order];
        #O termo pesquisado
        $term = $form['search']['value'];
        $query = SelectQuery::select('id,razao_social,fantasia,telefone,cnpj,ie,cep,cidade,estado')->from('empresa');
        if (!is_null($term) && ($term !== '')) {
            $query->where('empresa.razao_social', 'ilike', "%{$term}%", 'or')
                ->where('empresa.fantasia', 'ilike', "%{$term}%", 'or')
                ->where('empresa.telefone', 'ilike', "%{$term}%", 'or')
                ->where('empresa.cnpj', 'ilike', "%{$term}%", 'or')
                ->where('empresa.ie', 'ilike', "%{$term}%", 'or')
                ->where('empresa.cep', 'ilike', "%{$term}%", 'or')
                ->where('empresa.cidade', 'ilike', "%{$term}%", 'or')
                ->where('empresa.estado', 'ilike', "%{$term}%");
        }
        $users = $query
            ->order($orderField, $orderType)
            ->limit($length, $start)
            ->fetchAll();
        $userData = [];
        foreach ($users as $key => $value) {
            $userData[$key] = [
                $value['id'],
                $value['razao_social'],
                $value['fantasia'],
                $value['telefone'],
                $value['cnpj'],
                $value['ie'],
                $value['cep'],
                $value['cidade'],
                $value['estado'],
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