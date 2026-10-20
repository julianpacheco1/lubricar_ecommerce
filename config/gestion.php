<?php

return [
    'api_url' => rtrim(env('GESTION_API_URL', 'http://127.0.0.1:8000/api/v1/ecommerce'), '/'),
    'timeout' => (int) env('GESTION_API_TIMEOUT', 20),
];
