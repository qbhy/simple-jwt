# simple-jwt
超简单的 jwt 实现

## 如何安装 ？
```bash
composer require 96qbhy/simple-jwt
```

## 如何使用 ？
```php
require 'vendor/autoload.php';

use Qbhy\SimpleJwt\JWTManager;
use Qbhy\SimpleJwt\JWT;
use Qbhy\SimpleJwt\Encoders;
use Qbhy\SimpleJwt\EncryptAdapters;

$secret = '96qbhy/simple-jwt';

$headers = ['ver' => 0.1,];

$payload = [
    'user_id' => 'qbhy@gmail.com',
    'tester'  => 'qbhy',
];

// 可以使用自己实现的 encrypter 进行签名和校验，请继承自 AbstractEncrypter 抽象类
$encrypter = new EncryptAdapters\Md5Encrypter($secret);

// 可以使用自己实现的 encoder 进行编码，请实现 Encoder 接口
$encoder = new Encoders\Base64Encoder();

// 实例化 jwt manager
$jwtManager = new JWTManager($secret, $encoder);

// 设置 token 有效时间，单位 分钟
$jwtManager->setTtl(60);

// 通过 jwt manager 示例化 jwt ，标准 jwt
$jwt0 = $jwtManager->make($payload, $headers);

// 生成 token，当然你也可以使用链式调用，例如:  $jwtManager->make($payload, $headers)->token()
$token = $jwt0->token();
print_r($token);

// 通过 token 得到 jwt 对象
$jwt1 = $jwtManager->fromToken($token);

// 得到 payload
$jwt1->getPayload();

// 得到 headers
$jwt1->getHeaders();

print_r($jwt1);

// 自己实例化 jwt ，完全纯净的 jwt
$jwt2 = new JWT($headers, $payload, $secret);

// 仍然可以自定义 encoder 和 encrypter
$jwt2->setEncoder($encoder);
$jwt2->setEncrypter($encrypter);

```
[基本使用](/example.php)

96qbhy@gmail.com  