<?php

use App\Controllers\SortirAParisController;
use App\Controllers\germanytravelController;

$app->get('/sortiraparis', SortirAParisController::class . ':sortiraparis');
$app->get('/germanytravel', germanytravelController::class . ':germanytravel');






