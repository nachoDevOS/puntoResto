<?php

return [
    'ticket_service_url' => env('PRINT_TICKET_SERVICE_URL', 'http://localhost:3051'),
    'type' => env('PRINT_TICKET_TYPE', 'usb'),
    'ip' => env('PRINT_TICKET_IP'),
    'port' => env('PRINT_TICKET_PORT'),
    'template' => env('PRINT_TICKET_TEMPLATE', 'ticket'),
    'title' => env('PRINT_TICKET_BUSSINE', env('PRINT_TICKET_TITLE', env('APP_NAME', 'PuntoResto'))),
];
