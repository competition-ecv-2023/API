<?php
require '/vendor/autoload.php';
require '/v1/config/settings.php';
require '/v1/src/Api/Api.php';

$app = new Api\Api();

require 'api/v1/routes/routes.php';

$app->run();