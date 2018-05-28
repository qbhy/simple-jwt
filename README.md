# simple-jwt
超简单的 jwt 实现

## 如何安装 ？
```bash
composer require 96qbhy/simple-jwt
```

## 如何使用 ？
```php
require 'vendor/autoload.php';

$secret = '96qbhy/simple-jwt';

$headers = [
    'alg' => 'jwt',
    'ver' => 0.1,
];

$payload = [
    'user_id' => 'qbhy@gmail.com',
    'tester'  => 'qbhy',
];

$jwt = new \Qbhy\SimpleJwt\JWT($headers, $payload, $secret);

$token = $jwt->token();

print_r($token);

$decryptedJwt = \Qbhy\SimpleJwt\JWT::decryptToken($token, $secret);

print_r($decryptedJwt);

```
[基本使用](/example.php)

96qbhy@gmail.com  