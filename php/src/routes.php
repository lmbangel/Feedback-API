<?php
declare(strict_types=1);
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$app->get('/api/v1/test[/]', function (Request $request, Response $response) {
    $data = ['test' => 1];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
})->add($JWT_Middleware);

$app->group('/api/v1/auth', function (RouteCollectorProxy $group) {

    $group->post('/login[/]', function (Request $request, Response $response) {
        $req = $request->getBody()->getContents();
        $r = json_decode(stripcslashes($req), associative: true);
        $username = $r['user'] ?? null;
        $password = $r['password'] ?? null;

        if (!$username || !$password):
            $response->getBody()->write(json_encode(['error' => true, 'message' => 'Email and password required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        endif;

        $db = DB::connect();
        $q = "SELECT id, email, password_hash FROM users WHERE email = :email LIMIT 1;";
        $smnt = $db->prepare($q);
        $smnt->execute([':email' => $username]);
        $user = $smnt->fetch(\PDO::FETCH_ASSOC);

        if (!$user):
            $data = [
                'error' => true,
                'message' => "User does not exist.",
            ];
            $response->getBody()->write(json_encode($data ?? []));
            $response->withHeader('Content-Type', 'application/json');
            return $response->withStatus(404);
        endif;

        if (!\password_verify($password, $user['password_hash'])):
            $data = [
                'error' => true,
                'message' => "Incorrect password.",
            ];
            $response->getBody()->write(json_encode($data ?? []));
            $response->withHeader('Content-Type', 'application/json');
            return $response->withStatus(400);
        endif;

        $data = [
            'error' => false,
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        $jwt = JWT::encode($data, $_ENV['JWT_SECRET'], 'HS256');
        $data['token'] = $jwt;

        $q = "SELECT id, token, `expiry_timestamp` FROM refresh_tokens WHERE user_id = :user_id Limit 1;";
        $smnt = $db->prepare($q);
        $smnt->execute([':user_id' => $user['id']]);
        $user_refresh_token = $smnt->fetch(\PDO::FETCH_ASSOC);

        $refresh_token = bin2hex(random_bytes(64));
        if ($user_refresh_token):
            $q = "UPDATE refresh_tokens SET token = :token, `expiry_timestamp` = :expiry_timestamp WHERE user_id = :user_id;";
            $smnt = $db->prepare($q);
            $smnt->execute([':user_id' => $user['id'], ':token' => $refresh_token, ':expiry_timestamp' => \date('Y-m-d H:i:s', strtotime('+7 days'))]);
        else:
            $q = "INSERT INTO refresh_tokens (user_id, token, `expiry_timestamp`) VALUES (:user_id, :token, :expiry_timestamp);";
            $smnt = $db->prepare($q);
            $smnt->execute([':user_id' => $user['id'], ':token' => $refresh_token, ':expiry_timestamp' => \date('Y-m-d H:i:s', strtotime('+7 days'))]);
        endif;

        $response->getBody()->write(\json_encode($data));
        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Set-Cookie', "refresh_token={$refresh_token}; HttpOnly; Secure; SameSite=Strict; Path=/api/token/refresh");
        return $response->withStatus(200);
    });
    $group->post('/refresh[/]', function (Request $request, Response $response) {
        $cookies = $request->getCookieParams();
        $tkn = $cookies['refresh_token'] ?? null;

        if (!$tkn):
            $response->getBody()->write(json_encode(['error' => true, 'message' => 'Invalid request headers']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        endif;

        $db = DB::connect();
        $q = "SELECT u.id, u.email, r.token, r.`timestamp`, r.expiry_timestamp, (julianday(r.expiry_timestamp) - julianday(datetime('now'))) AS exp_days FROM refresh_tokens r 
        LEFT JOIN users u ON u.id = r.user_id WHERE r.token = :tkn LIMIT 1;";
        $smnt = $db->prepare($q);
        $smnt->execute([':tkn' => $tkn]);
        $user = $smnt->fetch(\PDO::FETCH_ASSOC);

        if (!$user):
            $response->getBody()->write(json_encode(['error' => true, 'message' => "Invalid token"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        endif;
        if ($user['exp_days'] <= 0):
            $response->getBody()->write(json_encode(['error' => true, 'message' => "Token Expired."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        endif;

        $data = [
            'error' => false,
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        $jwt = JWT::encode($data, $_ENV['JWT_SECRET'], 'HS256');
        $data['token'] = $jwt;

        $refresh_token = bin2hex(random_bytes(64));
        $q = "UPDATE refresh_tokens SET token = :token, `expiry_timestamp` = :expiry_timestamp WHERE user_id = :user_id;";
        $smnt = $db->prepare($q);
        $smnt->execute([':user_id' => $user['id'], ':token' => $refresh_token, ':expiry_timestamp' => \date('Y-m-d H:i:s', strtotime('+7 days'))]);

        $response->getBody()->write(\json_encode($data));
        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Set-Cookie', "refresh_token={$refresh_token}; HttpOnly; Secure; SameSite=Strict; Path=/api/token/refresh");
        return $response->withStatus(200);
    });
});