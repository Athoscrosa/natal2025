<?php

use app\controller\Home;
use app\controller\User;
use app\controller\Login;
use app\controller\Cliente;
use app\controller\Empresa;
use app\controller\Fornecedor;
use app\middleware\Middleware;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', Home::class . ':home')->add(Middleware::authentication());
$app->get('/home', Home::class . ':home')->add(Middleware::authentication());


$app->get('/login', Login::class . ':login');

$app->group('/login', function (RouteCollectorProxy $group) {
    $group->post('/precadastro', Login::class . ':precadastro');
    $group->post('/autenticar', Login::class . ':autenticar');
});

$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->get('/lista', User::class . ':lista')->add(Middleware::authentication());
    $group->get('/cadastro', User::class . ':cadastro')->add(Middleware::authentication());
    $group->get('/alterar/{id}', User::class . ':alterar');
    $group->post('/listuser', User::class . ':listuser');
    $group->post('/insert', User::class . ':insert');
    $group->post('/update', User::class . ':update');
});

$app->group('/cliente', function (RouteCollectorProxy $group) {
    $group->get('/lista', Cliente::class . ':lista')->add(Middleware::authentication());
    $group->get('/cadastro', Cliente::class . ':cadastro')->add(Middleware::authentication());
    $group->post('/insert', Cliente::class . ':insert');
    $group->post('/delete', Cliente::class . ':delete');
    $group->post('/listcliente', cliente::class . ':listcliente');
});

$app->group('/fornecedor', function (RouteCollectorProxy $group) {
    $group->get('/lista', Fornecedor::class . ':lista')->add(Middleware::authentication());
    $group->get('/cadastro', Fornecedor::class . ':cadastro')->add(Middleware::authentication());
    $group->post('/insert', Fornecedor::class . ':insert');
    $group->post('/delete', Fornecedor::class . ':delete');
    $group->post('/listfornecedor', Fornecedor::class . ':listfornecedor');
});

$app->group('/empresa', function (RouteCollectorProxy $group) {
    $group->get('/lista', Empresa::class . ':lista')->add(Middleware::authentication());
    $group->get('/cadastro', Empresa::class . ':cadastro')->add(Middleware::authentication());
    $group->post('/insert', Empresa::class . ':insert');
    $group->post('/delete', Empresa::class . ':delete');
    $group->post('/listempresa', empresa::class . ':listempresa');
});