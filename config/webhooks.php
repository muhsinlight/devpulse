<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inbound request retention
    |--------------------------------------------------------------------------
    |
    | Captured webhook_requests older than this many days are deleted daily.
    | Set to 0 to keep requests until the endpoint is deleted.
    |
    */

    'request_retention_days' => (int) env('WEBHOOK_REQUEST_RETENTION_DAYS', 30),

];
