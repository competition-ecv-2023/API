<?php
require '/vendor/autoload.php';
require '/v1/config/settings.php';

$app = new \Api(); // Exemple d'initialisation

require 'api/v1/routes/routes.php';

$app->run();