<?php
ob_start();

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
);

// Capture any output that happened before the response
$capturedOutput = ob_get_clean();

// Log what was captured
if (!empty($capturedOutput)) {
    error_log('Captured output before response: ' . json_encode($capturedOutput));
    file_put_contents(__DIR__ . '/debug_output.txt', $capturedOutput);
}

// Send the actual response
$response->send();

$kernel->terminate($request, $response);
