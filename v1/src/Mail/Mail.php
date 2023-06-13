<?php
namespace Mail;

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


//Load Composer's autoloader
include $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';


class Mail {
    /**
     * Permet d'envoyer des e-mails
     * 
     * @param string $emailAddress L'adresse e-mail du receveur
     * @param string $emailUsername Le pseudo du receveur
     * @param string $emailTemplate Modèle de l'email
     * @param array $emailParams Données du contenu de l'email si il y en a (optionnel)
     *  
     * @return bool Vrai si l'envoi de l'e-mail a réussi, faux sinon
     */
    public static function sendEmail(
        string $emailAddress,
        string $emailUsername,
        string $emailTemplate,
        array $emailParams = array()
    ) {
        $mail = new PHPMailer(true);                            
        try {
            //Server settings
            $mail->isSMTP();
            $mail->SMTPDebug = 2; 
            $mail->CharSet  = 'utf-8';
            $mail->Host = 'patperdue.fr';
            $mail->Port = 465;
            $mail->SMTPSecure = 'ssl'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'noreply@patperdue.fr';
            $mail->Password = '';

            $mail->setFrom('noreply@patperdue.fr', 'Pat\'Perdue');
            $mail->addAddress($emailAddress);

            if (file_exists("v1/src/Mail/template/$emailTemplate.php")) {
                include("v1/src/Mail/template/$emailTemplate.php");
            } else {
                error_log("[Mail.php] - Email non envoyé, template manquante : $emailTemplate.php", 0);
                return false;
            }
            $mail->Subject = $emailTitle;
            $mail->msgHTML($emailContent);
            if ($mail->send()) {
                return true;
            } else {
                error_log("[Mail.php] - Email non envoyé à : $emailAddress. Est-ce que la template $emailTemplate.php existe ?", 0);
                return false;
            }
        } catch (Exception $e) {
            error_log("[Mail.php] - Email Exception : $e", 0);
            return false;
        }
    }
}
?>