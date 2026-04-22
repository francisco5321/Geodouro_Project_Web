<?php

return [
    'appName' => 'GeoFlora Web',
    'backendBaseUrl' => getenv('BACKEND_BASE_URL') ?: 'http://127.0.0.1:8080',
    'backendTimeoutSeconds' => (int) (getenv('BACKEND_TIMEOUT_SECONDS') ?: 8),
    'backendConnectTimeoutSeconds' => (int) (getenv('BACKEND_CONNECT_TIMEOUT_SECONDS') ?: 2),
    'backendAuthTimeoutSeconds' => (int) (getenv('BACKEND_AUTH_TIMEOUT_SECONDS') ?: 10),
    'backendAuthStrategy' => getenv('BACKEND_AUTH_STRATEGY') ?: 'local-token',
    'backendTokenSecret' => getenv('APP_AUTH_TOKEN_SECRET') ?: 'geodouro-auth-key',
    'backendAuthRequired' => filter_var(getenv('BACKEND_AUTH_REQUIRED') ?: false, FILTER_VALIDATE_BOOLEAN),
    'backendUploadsPath' => getenv('BACKEND_UPLOADS_PATH') ?: 'D:\\Universidade\\3 ANO\\2 SEMESTRE\\Estagio\\Geodouro_Project\\backend\\backend-uploads',
    'adminUsernames' => array_values(array_filter(array_map('trim', explode(',', getenv('ADMIN_USERNAMES') ?: '')))),
];
