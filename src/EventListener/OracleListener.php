<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManager;

class OracleListener
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function postConnect()
    {
        $conn = $this->em->getConnection();

        $stmt = $conn->prepare('SET ROLE ALL');
        $stmt->executeQuery();
    }
}
