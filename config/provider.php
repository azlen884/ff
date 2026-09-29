<?php
/**
 * FF Panel V2 - Provider / API Dispatcher
 * Real HTTP cURL integration with Free Fire game topup providers
 */

function dispatchProviderOrder(array $provider, array $service, string $playerUid, string $orderNumber): array {
    if (empty($provider['is_enabled'])) {
        return [
            'success' => false,
            'status' => 'pending',
            'provider_order_id' => null,
            'message' => 'Provider is currently disabled in admin panel. Order queued for manual processing.',
            'raw_response' => json_encode(['info' => 'Provider disabled'])
        ];
    }

    if (empty($provider['api_url']) || empty($provider['api_key'])) {
        return [
            'success' => false,
            'status' => 'pending',
            'provider_order_id' => null,
            'message' => 'Provider API credentials not configured. Queued for manual fulfillment.',
            'raw_response' => json_encode(['info' => 'Missing API URL or Key'])
        ];
    }

    $apiUrl = rtrim($provider['api_url'], '/');
    $providerServiceId = $service['provider_service_id'] ?? $service['id'];

    $payload = [
        'order_id' => $orderNumber,
        'service_id' => $providerServiceId,
        'uid' => $playerUid,
        'api_key' => $provider['api_key']
    ];

    if (!empty($provider['api_secret'])) {
        $payload['sign'] = hash_hmac('sha256', $orderNumber . $playerUid . $providerServiceId, $provider['api_secret']);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '/order');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $provider['api_key']
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return [
            'success' => false,
            'status' => 'failed',
            'provider_order_id' => null,
            'message' => 'Provider network error: ' . $curlError,
            'raw_response' => json_encode(['curl_error' => $curlError, 'http_code' => $httpCode])
        ];
    }

    $parsed = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300 && is_array($parsed) && (!isset($parsed['status']) || in_array(strtolower((string)$parsed['status']), ['success', '1', 'completed', 'true', 'ok']))) {
        $pOrderId = $parsed['order_id'] ?? $parsed['transaction_id'] ?? $parsed['id'] ?? 'PRV-' . strtoupper(substr(uniqid(), -6));
        return [
            'success' => true,
            'status' => 'completed',
            'provider_order_id' => (string)$pOrderId,
            'message' => 'Order fulfilled successfully via ' . ($provider['name'] ?? 'Provider API'),
            'raw_response' => $response
        ];
    } else {
        $errMsg = $parsed['message'] ?? $parsed['error'] ?? 'Provider returned HTTP ' . $httpCode;
        return [
            'success' => false,
            'status' => 'failed',
            'provider_order_id' => null,
            'message' => 'Provider rejected: ' . $errMsg,
            'raw_response' => $response
        ];
    }
}
