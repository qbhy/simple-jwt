<?php

declare(strict_types=1);
/**
 * This file is part of qbhy/simple-jwt.
 *
 * @link     https://github.com/qbhy/simple-jwt
 * @document https://github.com/qbhy/simple-jwt/blob/master/README.md
 * @contact  qbhy0715@qq.com
 * @license  https://github.com/qbhy/simple-jwt/blob/master/LICENSE
 */
namespace Qbhy\SimpleJwt\Hyperf;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Utils\ApplicationContext;
use Qbhy\SimpleJwt\JWTManager;

class ConfigProvider
{
    public function __invoke(): array
    {
        if (ApplicationContext::hasContainer()) {
            /** @var ContainerInterface $container */
            $container = ApplicationContext::getContainer();
            $container->define(JWTManager::class, function () use ($container) {
                return new JWTManager(
                    $container->get(ConfigInterface::class)->get('simple-jwt', ['secret' => env('SIMPLE_JWT_SECRET')])
                );
            });
        }

        return [
            // 合并到  config/autoload/dependencies.php 文件
            'dependencies' => [],
            // 合并到  config/autoload/annotations.php 文件
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // 默认 Command 的定义，合并到 Hyperf\Contract\ConfigInterface 内，换个方式理解也就是与 config/autoload/commands.php 对应
            'commands' => [],
            // 与 commands 类似
            'listeners' => [],
            // 组件默认配置文件，即执行命令后会把 source 的对应的文件复制为 destination 对应的的文件
            'publish' => [
                [
                    'id' => 'simple-jwt',
                    'description' => 'simple jwt 配置文件。', // 描述
                    // 建议默认配置放在 publish 文件夹中，文件命名和组件名称相同
                    'source' => __DIR__ . '/../Laravel/config/simple-jwt.php',  // 对应的配置文件路径
                    'destination' => BASE_PATH . '/config/autoload/simple-jwt.php', // 复制为这个路径下的该文件
                ],
            ],
            // 亦可继续定义其它配置，最终都会合并到与 ConfigInterface 对应的配置储存器中
        ];
    }
}
