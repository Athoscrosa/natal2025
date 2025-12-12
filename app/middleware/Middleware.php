<?php

namespace app\middleware;

class Middleware
{
    public static function authentication()
    {
        #Retorna um closure (função anônima)
        $middleware = function ($request, $handler) {
            $response = $handler->handle($request);
            #Capturamos o metodo de requisição
            $method = $request->getMethod();
            #Capturamos a pagina que o usuário está tentando acessar.
            $pagina = $request->getRequestTarget();
            if ($method === 'GET') {
                # Verifica se o usuário NÃO está autenticado
                # Condições: sessão vazia OU flag 'logado' false OU inexistente
                $usuarioLogado = empty($_SESSION['usuario']) || empty($_SESSION['usuario']['logado']);
                # Se usuário não está logado E não está tentando acessar a página de login
                if ($usuarioLogado and $pagina !== '/login') {
                    # Destroi a sessão para limpar qualquer dado residual
                    session_destroy();
                    # Redireciona para a página de login com status HTTP 302 (redirecionamento temporário)
                    return $response->withHeader('Location', '/login')->withStatus(302);
                }
                # Se a página solicitada é a de login
                if ($pagina === '/login') {

                    if (!$usuarioLogado) {
                        return $response->withHeader('Location', '/')->withStatus(302);
                    }
                }
                if (empty($_SESSION['usuario']['ativo']) or !$_SESSION['usuario']['ativo']) {
                    session_destroy();
                    return $response->withHeader('Location', '/login')->withStatus(302);
                }
            }
            return $handler->handle($request);
        };
        return $middleware;
    }
}