<?php
declare(strict_types=1);

use Doctrine\DBAL\DriverManager;

namespace App\DAL;

class CharacterDAL
{
    private $conn;

    public function __construct($configs)
    {
        $this->conn = DriverManager::getConnection($params, $config);
    }

    public function register($email, $password)
    {
        // The user just registered, we create his account
        // ...

        // We send him an email to say hello!
        $this->mailer->mail($email, 'Hello and welcome!');
    }
}