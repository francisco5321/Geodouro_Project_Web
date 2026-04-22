<?php

namespace app\components;

use RuntimeException;

class BackendApiClient
{
    public string $baseUrl = '';
    public int $timeoutSeconds = 8;
    public int $connectTimeoutSeconds = 2;

    public function getJson(string $path, array $headers = [], ?int $timeoutSeconds = null): array
    {
        return $this->request('GET', $path, null, $headers, $timeoutSeconds);
    }

    public function postJson(string $path, array $payload, array $headers = [], ?int $timeoutSeconds = null): array
    {
        return $this->request('POST', $path, $payload, $headers, $timeoutSeconds);
    }

    public function patchJson(string $path, array $payload, array $headers = [], ?int $timeoutSeconds = null): array
    {
        return $this->request('PATCH', $path, $payload, $headers, $timeoutSeconds);
    }

    public function deleteJson(string $path, array $headers = [], ?int $timeoutSeconds = null): array
    {
        return $this->request('DELETE', $path, null, $headers, $timeoutSeconds);
    }

    private function request(string $method, string $path, ?array $payload, array $headers, ?int $timeoutSeconds = null): array
    {
        $baseUrl = rtrim(trim($this->baseUrl), '/');
        if ($baseUrl === '') {
            throw new RuntimeException('Backend base URL is not configured.');
        }

        $url = $baseUrl . '/' . ltrim($path, '/');
        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('Could not initialize backend request.');
        }

        $requestHeaders = ['Accept: application/json'];
        foreach ($headers as $name => $value) {
            if ($value === null || trim((string) $value) === '') {
                continue;
            }
            $requestHeaders[] = sprintf('%s: %s', $name, $value);
        }

        $timeout = max(1, $timeoutSeconds ?? $this->timeoutSeconds);
        $connectTimeout = max(1, min($timeout, $this->connectTimeoutSeconds));

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
        ]);

        if ($payload !== null) {
            $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                throw new RuntimeException('Could not encode backend request payload.');
            }
            $requestHeaders[] = 'Content-Type: application/json';
            curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeaders);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded);
        }

        $rawBody = curl_exec($curl);
        $curlError = curl_error($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($rawBody === false) {
            throw new RuntimeException($curlError !== '' ? $curlError : 'Backend request failed.');
        }

        $body = trim((string) $rawBody);
        $decoded = $body === '' ? [] : json_decode($body, true);
        if ($body !== '' && !is_array($decoded)) {
            throw new RuntimeException('Backend response is not valid JSON.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = is_array($decoded) ? ($decoded['message'] ?? $decoded['error'] ?? null) : null;
            throw new RuntimeException($message ?: sprintf('Backend request failed with HTTP %d.', $statusCode));
        }

        return $decoded;
    }
}
