<?php
require_once "Micron2/Micron2.php";
require_once "App/Services/TestService.php";


$appBuilder = new WebApplicationBuilder();

$appBuilder->AddScoped(TestService::class);

$app = $appBuilder->Build();

$app->Start();