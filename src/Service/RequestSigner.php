<?php

namespace Ang3\Bundle\AwsApiGatewayBundle\Service;

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use Psr\Http\Message\RequestInterface;

class RequestSigner
{
    public function __construct(private Credentials $credentials)
    {
    }

    public function sign(RequestInterface $request, string $service, string $region): RequestInterface
    {
        return $this
            ->createSignatureV4($service, $region)
            ->signRequest($request, $this->credentials);
    }

    public function createSignatureV4(string $service, string $region): SignatureV4
    {
        return new SignatureV4($service, $region);
    }
}
