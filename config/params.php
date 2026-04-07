<?php

return [
    'appName' => 'GeoFlora Web',
    'backendUploadsPath' => getenv('BACKEND_UPLOADS_PATH') ?: 'D:\\Universidade\\3 ANO\\2 SEMESTRE\\Estagio\\Geodouro_Project\\backend\\backend-uploads',
    'adminUsernames' => array_values(array_filter(array_map('trim', explode(',', getenv('ADMIN_USERNAMES') ?: '')))),
];
