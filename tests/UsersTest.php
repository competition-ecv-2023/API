<?php
require_once 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;
include "v1/src/Database/Users.php";
use Database\Users;

class UsersTest extends TestCase {  

    /** @test */
    public function testLogin()
    {
        // Test with incorrect credentials
        $result = Users::login('goodemail@gmail.com', 'wrongpassword');
        $this->assertEquals(3, $result);

        // Test with invalid email
        $result = Users::login('invalidemail', 'password');
        $this->assertEquals(3, $result);
    }

    /** @test */
    public function testVerifyToken()
    {
        // Create token for test
        Users::createToken('frozplay.609@gmail.com');

        // Test with valid token
        $result = Users::verifyToken('frozplay.609@gmail.com', $_SESSION['user']['token']);
        $this->assertEquals(0, $result);
    }

    
    public function testRegister()
    {
        // Test pour un nom d'utilisateur qui ne correspond pas au format requis
        $result = Users::register("te", "Aa1@aaaa", "Aa1@aaaa", "test@example.com");
        $this->assertEquals(3, $result);

        // Test pour un mot de passe qui ne correspond pas au format requis
        $result = Users::register("Wl_Ie23mL_8e", "password", "password", "test@example.com");
        $this->assertEquals(4, $result);

        // Test pour un mot de passe et une confirmation de mot de passe qui ne correspondent pas
        $result = Users::register("Wl_Ie23mL_8e", "Aa1@aaaa", "Aa2@aaaa", "test@example.com");
        $this->assertEquals(5, $result);

        // Test pour une adresse email qui n'est pas au format valide
        $result = Users::register("Wl_Ie23mL_8e", "Aa1@aaaa", "Aa1@aaaa", "test.com");
        $this->assertEquals(6, $result);

        // Test pour une adresse email qui a déjà un compte vérifié associé
        $result = Users::register("Wl_Ie23mL_8e", "Aa1@aaaa", "Aa1@aaaa", "frozplay.609@gmail.com");
        $this->assertEquals(7, $result);
    }
}