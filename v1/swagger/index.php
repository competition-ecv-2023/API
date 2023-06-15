<?php

require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

$swagger = \Swagger\scan('/chemin/vers/votre/fichier.php');
file_put_contents('/chemin/vers/votre/swagger.json', $swagger);

require '';