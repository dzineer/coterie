<?php
declare(strict_types=1);

use Doctrine\DBAL\DriverManager;

namespace App\DAL;

class UserDAL
{
    private $conn;

    public function __construct($configs)
    {
        $this->conn = DriverManager::getConnection($params, $config);
    }

    public function get_cycle($dates = 0) {
        $query = $conn->createQueryBuilder();
        $query->select('*')
            ->from('cycles')
        $next = query1("SELECT * FROM  `cycles` WHERE  `date` > CURRENT_TIMESTAMP ORDER BY  `date` ASC LIMIT 1");
        if ($dates > 0) return $next;
        return $next['cycle'];
    }

    public function register($email, $password)
    {
        // The user just registered, we create his account
        // ...

        // We send him an email to say hello!
        $this->mailer->mail($email, 'Hello and welcome!');
    }
}