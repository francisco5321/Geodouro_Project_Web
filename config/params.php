<?php

return [
    'appName' => 'GeoFlora Web',
    'backendBaseUrl' => getenv('BACKEND_BASE_URL') ?: 'http://127.0.0.1:8080',
    'backendTimeoutSeconds' => (int) (getenv('BACKEND_TIMEOUT_SECONDS') ?: 15),
    'backendUploadsPath' => getenv('BACKEND_UPLOADS_PATH') ?: 'D:\\Universidade\\3 ANO\\2 SEMESTRE\\Estagio\\Geodouro_Project\\backend\\backend-uploads',
    'adminUsernames' => array_values(array_filter(array_map('trim', explode(',', getenv('ADMIN_USERNAMES') ?: '')))),
];
