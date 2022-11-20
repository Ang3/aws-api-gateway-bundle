<?php

namespace Ang3\Bundle\AwsApiGatewayBundle\Response;

use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public function __construct(protected int $code = Response::HTTP_OK,
                                protected array $data = [],
                                private array $context = [])
    {
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getLogGroupName(): ?string
    {
        return $this->getContextParameter('logGroupName');
    }

    public function getLogStreamName(): ?string
    {
        return $this->getContextParameter('logStreamName');
    }

    public function getContextParameter(string $key): ?string
    {
        return $this->context[$key] ?? null;
    }

    public function isFailed(): bool
    {
        return !$this->isSuccessful();
    }

    public function isSuccessful(): bool
    {
        return $this->code >= 200 && $this->code <= 299;
    }
}
