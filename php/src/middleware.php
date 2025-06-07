<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$JWT_Middleware = function (Request $req, $handler) {
    $auth_header = $req->getHeaderLine("Authorization");
    $jwt_secret = $_ENV['JWT_SECRET'];

    if (!$auth_header || !\str_starts_with($auth_header, "Bearer ")):
        $data = ['error' => true, 'message' => "Unauthorized"];
        $response = new Slim\Psr7\Response();
        $response->getBody()->write(json_encode($data));
        $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        return $response;
    endif;
    $tkn = \str_replace('Bearer ', '', $auth_header);

    try {
        $decode = JWT::decode($tkn, new Key($jwt_secret, 'HS256'));
        $req = $req->withAttribute('jwt', $decode);
    } catch (\Exception $e) {
        $response = new Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => true, 'message' => 'Invalid token']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    return $handler->handle($req);
};