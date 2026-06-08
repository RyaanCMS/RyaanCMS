<?php
/**
 * API Design Knowledge Base
 * REST standards, versioning, authentication, pagination, error handling
 */
return [

    // ── REST Resource Conventions ─────────────────────────────────────────────
    'rest_conventions' => [
        'endpoints' => [
            'GET    /api/v1/users'          => 'List (paginated)',
            'POST   /api/v1/users'          => 'Create',
            'GET    /api/v1/users/{id}'     => 'Show',
            'PUT    /api/v1/users/{id}'     => 'Full update',
            'PATCH  /api/v1/users/{id}'     => 'Partial update',
            'DELETE /api/v1/users/{id}'     => 'Delete',
            'POST   /api/v1/users/{id}/activate' => 'Custom action (verb in URL)',
        ],
        'naming_rules' => [
            'Use nouns, not verbs in resource paths',
            'Plural resource names: /users not /user',
            'Nested resources for relationships: /users/{id}/orders',
            'kebab-case for multi-word: /order-items not /orderItems',
            'Query params for filtering/sorting: ?status=active&sort=created_at&order=desc',
        ],
    ],

    // ── Response Format Standard ──────────────────────────────────────────────
    'response_format' => [
        'success_single' => [
            'success' => true,
            'message' => 'User created successfully',
            'data'    => ['id' => 1, 'name' => 'John'],
        ],
        'success_list' => [
            'success' => true,
            'data'    => [],
            'meta'    => ['current_page' => 1, 'total' => 100, 'per_page' => 25, 'last_page' => 4],
            'links'   => ['first' => '...', 'prev' => null, 'next' => '...', 'last' => '...'],
        ],
        'error_validation' => [
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => ['email' => ['Email is required'], 'name' => ['Name too long']],
        ],
        'error_generic' => [
            'success' => false,
            'message' => 'Resource not found',
            'code'    => 'NOT_FOUND',
        ],
        'http_status_codes' => [
            200 => 'OK — successful GET, PUT, PATCH',
            201 => 'Created — successful POST',
            204 => 'No Content — successful DELETE',
            400 => 'Bad Request — malformed request',
            401 => 'Unauthorized — not authenticated',
            403 => 'Forbidden — authenticated but not authorized',
            404 => 'Not Found — resource does not exist',
            409 => 'Conflict — duplicate resource',
            422 => 'Unprocessable Entity — validation failed',
            429 => 'Too Many Requests — rate limited',
            500 => 'Internal Server Error — something broke on server',
        ],
    ],

    // ── Versioning Strategy ───────────────────────────────────────────────────
    'versioning' => [
        'method'          => 'URL prefix: /api/v1/, /api/v2/',
        'rule'            => 'NEVER remove a field from v1 response — add in v2 if breaking change needed',
        'deprecation'     => 'Add Deprecation header and sunset date before removing old version',
        'default_version' => '/api → redirect to /api/v1 or use Accept header',
    ],

    // ── Authentication ────────────────────────────────────────────────────────
    'authentication' => [
        'sanctum_spa'     => 'SPA on same domain: cookie + CSRF token',
        'sanctum_mobile'  => 'Mobile/external: Bearer token in Authorization header',
        'token_expiry'    => 'Access token: 15min–1hr. Refresh token: 30 days.',
        'token_scopes'    => "['read:users', 'write:orders'] — grant minimal permissions",
        'header_format'   => 'Authorization: Bearer {token}',
        'refresh_flow'    => 'POST /api/v1/auth/refresh with refresh_token → new access token',
    ],

    // ── Pagination ────────────────────────────────────────────────────────────
    'pagination' => [
        'default_type'   => 'Cursor pagination (no COUNT query) for feeds/infinite scroll',
        'page_based'     => 'Use for admin tables where "Jump to page X" is needed',
        'cursor_based'   => "Use cursor= param: GET /api/v1/orders?cursor=eyJpZCI6MTAwfQ",
        'max_per_page'   => 100,
        'default_per_page'=> 25,
    ],

    // ── Rate Limiting ─────────────────────────────────────────────────────────
    'rate_limiting' => [
        'public_routes'  => '30 requests/minute per IP',
        'auth_routes'    => '600 requests/minute per user',
        'upload_routes'  => '10 requests/minute per user',
        'response_headers'=> ['X-RateLimit-Limit', 'X-RateLimit-Remaining', 'X-RateLimit-Reset'],
        'throttle_response'=> 'HTTP 429 with Retry-After header',
    ],

    // ── Webhook Design ────────────────────────────────────────────────────────
    'webhooks' => [
        'delivery'        => 'POST to client URL with JSON payload + signature header',
        'signing'         => 'HMAC-SHA256 signature in X-Signature header — clients must verify',
        'retry'           => 'Retry with exponential backoff: 5s, 30s, 2min, 10min, 30min, 2hr',
        'payload_format'  => ['event' => 'order.paid', 'data' => [], 'timestamp' => 'ISO8601', 'id' => 'uuid'],
        'idempotency'     => 'Each event has unique ID — clients must handle duplicate delivery',
    ],

    // ── API Documentation ─────────────────────────────────────────────────────
    'documentation' => [
        'format'  => 'OpenAPI 3.0 (Swagger)',
        'package' => 'knuckleswtf/scribe — auto-generate from docblocks and Form Requests',
        'must_document' => ['Request parameters', 'Response schema', 'Error codes', 'Authentication'],
    ],

    // ── API Resources (Laravel) ────────────────────────────────────────────────
    'api_resources' => [
        'rule'        => 'ALWAYS use API Resources — never return raw Eloquent models',
        'collection'  => 'Extend ResourceCollection for custom meta in list responses',
        'conditional' => 'whenLoaded() for relationships — avoid over-fetching',
        'example'     => "return new UserResource(\$user) // transforms + hides sensitive fields",
    ],

];
