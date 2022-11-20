<?php

namespace Ang3\Bundle\AwsApiGatewayBundle\Service;

use Ang3\Bundle\AwsApiGatewayBundle\Response\ApiResponse;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Iterator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ApiClient
{
    /**
     * Client options.
     */
    public const MAX_CONCURRENCY_LIMIT = 500;
    public const REQUEST_TIMEOUT = 30;

    private Client $client;

    public function __construct(private RequestSigner $requestSigner,
                                private LoggerInterface $logger,
                                private string $region)
    {
        $this->client = new Client();
    }

    /**
     * @throws ApiRequestException when the function failed
     */
    public function call(string $url, string $method = 'GET', array $data = [], array $options = []): ApiResponse
    {
        $request = $this->createSignedRequest($url, $method, $data, $context);
        $options = $this->getOptions($options, $context);
        $this->logger->info('Synchronous execution of lambda function "{name}"...', $context);

        try {
            $response = $this->client->send($request, $options);
        } catch (GuzzleException $e) {
            $context['error'] = $e->getMessage();
            $this->logger->error('Synchronous execution of lambda function "{name}" failed - {error}.', $context);
            throw new ApiRequestException(sprintf('The execution of lambda function "%s" failed.', $context['name']), 0, $e);
        }

        return $this->parseResponse($response);
    }

    public function callAsync(string $url, string $method = 'GET', array $data = [], array $options = []): PromiseInterface
    {
        $request = $this->createSignedRequest($url, $method, $data, $context);
        $options = $this->getOptions($options, $context);
        $this->logger->info('Asynchronous execution of lambda function "{name}"...', $context);

        return $this->client->sendAsync($request, $options);
    }

    public function multiCall(array|Iterator $requests, callable $fulfilled, callable $rejected): void
    {
        $requestPool = new Pool($this->client, $requests, [
            'concurrency' => self::MAX_CONCURRENCY_LIMIT,
            'fulfilled' => $fulfilled,
            'rejected' => $rejected,
            'options' => [
                'timeout' => self::REQUEST_TIMEOUT,
            ],
        ]);

        // Initiate the transfers and create a promise
        $promise = $requestPool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }

    /**
     * @throws ApiRequestException on response data decoding failure
     */
    public function parseResponse(ResponseInterface $response): ApiResponse
    {
        try {
            $contents = $response->getBody()->getContents();
            $payload = json_decode($contents, true);

            if (false === $payload) {
                throw new \RuntimeException(json_last_error_msg(), json_last_error());
            }
        } catch (Exception $e) {
            throw new ApiRequestException('Failed to decode data from the response.', 0, $e);
        }

        $payload = is_array($payload) ? $payload : [$payload];
        $context = $payload['context'] ?? [];
        if (array_key_exists('context', $payload)) {
            unset($payload['context']);
        }

        return new ApiResponse($response->getStatusCode(), $payload, $context);
    }

    public function createSignedRequest(string $url, string $method = 'GET', array $data = [], array &$context = null): RequestInterface
    {
        $context = [
            'endpoint' => $url,
            'method' => $method,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'data' => $data,
        ];

        $contents = json_encode($data);

        if (false === $contents) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        $context['contents'] = $contents;
        $request = new Request($method, $context['endpoint'], $context['headers'], $contents);

        return $this->requestSigner->sign($request, 'execute-api', $this->region);
    }

    /**
     * @internal
     */
    private function getOptions(array $options = [], array &$context = []): array
    {
        $options = array_merge($options, [
            'http_errors' => false,
        ]);

        $context['client_options'] = $options;

        return $options;
    }
}
