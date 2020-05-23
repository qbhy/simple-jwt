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
namespace Qbhy\SimpleJwt;

/**
 * User: qbhy
 * Date: 2018/5/28
 * Time: 下午12:06.
 */
class JWT
{
    /** @var array */
    protected $headers = [
        'typ' => 'jwt',
    ];

    /** @var array */
    protected $payload = [];

    /**
     * @var JWTManager
     */
    protected $manager;

    /**
     * JWT constructor.
     */
    public function __construct(JWTManager $manager, array $headers, array $payload)
    {
        $this->manager = $manager;
        $this->headers = array_merge($this->headers, $headers);
        $this->payload = $payload;
    }

    public function token(): string
    {
        $signatureString = $this->generateSignatureString();

        $signature = $this->manager->getEncoder()->encode(
            $this->manager->getEncrypter()->signature($signatureString)
        );

        return "{$signatureString}.{$signature}";
    }

    public function generateSignatureString(): string
    {
        $headersString = $this->manager->getEncoder()->encode(json_encode($this->headers));
        $payloadString = $this->manager->getEncoder()->encode(json_encode($this->payload));

        return "{$headersString}.{$payloadString}";
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
