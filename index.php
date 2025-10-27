<?php
require_once "Micron2/Micron2.php";
require_once "App/Services/TestService.php";
require_once "App/Middlewares/CommonResponseMiddleware.php";


$appBuilder = new WebApplicationBuilder();

$appBuilder->AddScoped(TestService::class);

$appBuilder->AddConfigurations($_SERVER['DOCUMENT_ROOT']);

$app = $appBuilder->Build();

$app->AddEndpoints();

$app->AddMiddleware(CommonResponseMiddleware::class);

$app->Start();