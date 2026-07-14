<?php

declare(strict_types=1);

class SupabaseClient
{
    /**
     * @param array<string, mixed>|null $payload
     * @return array{ok: bool, status: int, data: array<mixed>|array<string, mixed>|null, error: string|null}
     */
    public function postAuth(string $path, ?array $payload = null): array
    {
        return $this->request(
            supabase_auth_url() . '/' . ltrim($path, '/'),
            'POST',
            $payload,
            $this->authHeaders()
        );
    }

    /**
     * @return array{ok: bool, status: int, data: array<mixed>|array<string, mixed>|null, error: string|null}
     */
    public function getRest(string $table, string $query = ''): array
    {
        $url = supabase_rest_url() . '/' . $table;

        if ($query !== '') {
            $url .= '?' . $query;
        }

        return $this->request($url, 'GET', null, $this->serviceHeaders());
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{ok: bool, status: int, data: array<mixed>|array<string, mixed>|null, error: string|null}
     */
    public function postRest(string $table, array $payload): array
    {
        $headers = $this->serviceHeaders();
        $headers[] = 'Prefer: return=representation';

        return $this->request(
            supabase_rest_url() . '/' . $table,
            'POST',
            $payload,
            $headers
        );
    }

    /**
     * @return array<int, string>
     */
    private function authHeaders(): array
    {
        $anonKey = env('SUPABASE_ANON_KEY', '');

        return [
            'apikey: ' . $anonKey,
            'Authorization: Bearer ' . $anonKey,
            'Content-Type: application/json',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function serviceHeaders(): array
    {
        $serviceRoleKey = env('SUPABASE_SERVICE_ROLE_KEY', '');

        return [
            'apikey: ' . $serviceRoleKey,
            'Authorization: Bearer ' . $serviceRoleKey,
            'Content-Type: application/json',
        ];
    }

    /**
     * @param array<string, mixed>|null $payload
     * @param array<int, string> $headers
     * @return array{ok: bool, status: int, data: array<mixed>|array<string, mixed>|null, error: string|null}
     */
    private function request(string $url, string $method, ?array $payload, array $headers): array
    {
        if ($url === '' || str_starts_with($url, '/auth/v1/') || str_starts_with($url, '/rest/v1/')) {
            return [
                'ok' => false,
                'status' => 0,
                'data' => null,
                'error' => 'Supabase environment variables are missing.',
            ];
        }

        $curlResult = $this->requestWithCurl($url, $method, $payload, $headers);

        if ($curlResult['ok'] || $curlResult['status'] !== 0) {
            return $curlResult;
        }

        return $this->requestWithStream($url, $method, $payload, $headers, $curlResult['error']);
    }

    /**
     * @param array<string, mixed>|null $payload
     * @param array<int, string> $headers
     * @return array{ok: bool, status: int, data: array<mixed>|array<string, mixed>|null, error: string|null}
     */
    private function requestWithCurl(string $url, string $method, ?array $payload, array $headers): array
    {
        $ch = curl_init($url);

        if ($ch === false) {
            return [
                'ok' => false,
                'status' => 0,
                'data' => null,
                'error' => 'Unable to initialize cURL.',
            ];
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_NOPROXY, '*');

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        }

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            return [
                'ok' => false,
                'status' => $status,
                'data' => null,
                'error' => $curlError !== '' ? $curlError : 'Unknown network error.',
            ];
        }

        $decoded = json_decode($body, true);

        if (is_array($decoded) && isset($decoded['msg']) && is_string($decoded['msg'])) {
            $error = $decoded['msg'];
        } elseif (is_array($decoded) && isset($decoded['error_description']) && is_string($decoded['error_description'])) {
            $error = $decoded['error_description'];
        } elseif (is_array($decoded) && isset($decoded['message']) && is_string($decoded['message'])) {
            $error = $decoded['message'];
        } else {
            $error = null;
        }

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => is_array($decoded) ? $decoded : null,
            'error' => $error,
        ];
    }

    /**
     * @param array<string, mixed>|null $payload
     * @param array<int, string> $headers
     * @return array{ok: bool, status: int, data: array<mixed>|array<string, mixed>|null, error: string|null}
     */
    private function requestWithStream(
        string $url,
        string $method,
        ?array $payload,
        array $headers,
        ?string $fallbackError
    ): array {
        $body = $payload === null ? null : json_encode($payload, JSON_THROW_ON_ERROR);
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body === null ? '' : $body,
                'ignore_errors' => true,
                'timeout' => 20,
            ],
        ]);

        $responseBody = @file_get_contents($url, false, $context);
        $responseHeaders = $http_response_header ?? [];
        $statusLine = $responseHeaders[0] ?? '';
        $status = 0;

        if (preg_match('/\s(\d{3})\s/', $statusLine, $matches) === 1) {
            $status = (int) $matches[1];
        }

        if ($responseBody === false) {
            $lastError = error_get_last();

            return [
                'ok' => false,
                'status' => $status,
                'data' => null,
                'error' => $lastError['message'] ?? $fallbackError ?? 'Unknown network error.',
            ];
        }

        $decoded = json_decode($responseBody, true);
        $error = null;

        if (is_array($decoded) && isset($decoded['msg']) && is_string($decoded['msg'])) {
            $error = $decoded['msg'];
        } elseif (is_array($decoded) && isset($decoded['error_description']) && is_string($decoded['error_description'])) {
            $error = $decoded['error_description'];
        } elseif (is_array($decoded) && isset($decoded['message']) && is_string($decoded['message'])) {
            $error = $decoded['message'];
        }

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => is_array($decoded) ? $decoded : null,
            'error' => $error,
        ];
    }
}
