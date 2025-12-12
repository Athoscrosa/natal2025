<?php

namespace app\controller;

class Home extends Base
{
    public function home($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Página Inicial'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('home'), $dadosTemplate)
            ->withHeader('Content-type', 'text/html')
            ->withStatus(200);
    }
}