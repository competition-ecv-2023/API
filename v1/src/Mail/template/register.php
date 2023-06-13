<?php
foreach($emailParams as $key => $value) {
    $$key = $value; //$verification_code_email
}

$emailTitle = "Pat'Perdue - Vérification de l'adresse email.";
$emailContent = "<h3>Bienvenue sur Pat'Perdue !</h3>";
$emailContent .= "Voici le code permettant de vérifier votre adresse email : <b>$verification_code_email</b><br>";
$emailContent .= "Attention, ce code est valable uniquement 5 minutes !";
?>