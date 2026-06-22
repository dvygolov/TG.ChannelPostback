<?php

use App\Logger;

/**
 * Display formatted error and exit
 */
function error_response(string $message, int $code = 400): never
{
    Logger::error($message);
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

/**
 * Display success JSON response
 */
function success_response(mixed $data = [], string $message = 'Success'): never
{
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Redirect helper
 */
function redirect(string $url, int $code = 302): never
{
    header("Location: {$url}", true, $code);
    exit;
}

/**
 * Escape HTML
 */
function e(?string $string): string
{
    return $string ? htmlspecialchars($string, ENT_QUOTES, 'UTF-8') : '';
}

/**
 * Check if request is POST
 */
function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 */
function is_get(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Get POST data
 */
function post(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $default;
}

/**
 * Get GET data
 */
function get(string $key, mixed $default = null): mixed
{
    return $_GET[$key] ?? $default;
}

/**
 * Validate required fields
 */
function validate_required(array $fields, array $data): array
{
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[] = "Field '{$field}' is required";
        }
    }
    return $errors;
}

/**
 * Global translation helper
 */
function __(string $key, string $default = ''): string
{
    return \App\Locale::t($key, $default);
}
