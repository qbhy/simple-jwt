# simple-jwt

超简单的 jwt 实现。支持 Laravel、Hyperf 以及支持 composer 的其他框架

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
use Qbhy\SimpleJwt\Exceptions;

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
$cache = function(JWTManager $JWTManager){
return new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir());
};

// 实例化 jwt manager
$jwtManager = new JWTManager(compact('secret', 'encode','cache'));

// 设置 token 有效时间，单位 分钟
$jwtManager->setTtl(60);
// 设置 token 过期后多久时间内允许刷新，单位 分钟
$jwtManager->setRefreshTtl(120);

// 通过 jwt manager 实例化 jwt ，标准 jwt
$jwt0 = $jwtManager->make($payload, $headers);

// 生成 token，当然你也可以使用链式调用，例如:  $jwtManager->make($payload, $headers)->token()
$token = $jwt0->token();
print_r($token);

try {
// 通过 token 得到 jwt 对象
    $jwt1 = $jwtManager->parse($token);
    
    // 单纯解析，只判断签名是否正确，不判断时效性和黑名单 
//    $jwt1 = $jwtManager->justParse($token);
} catch (Exceptions\TokenExpiredException $tokenExpiredException) {
    // 如果已经过期了，也可以尝试刷新此 jwt ,第二个参数如果为 true 将忽略 refresh ttl 检查
    $jwt1 = $jwtManager->refresh($tokenExpiredException->getJwt(), true);
}

// 得到 payload
$jwt1->getPayload();

// 得到 headers
$jwt1->getHeaders();

print_r($jwt1);

// 自己实例化 jwt ，完全纯净的 jwt ，无多余 payload
$jwt2 = new JWT($jwtManager, $headers, $payload);
```

96qbhy@gmail.com  
