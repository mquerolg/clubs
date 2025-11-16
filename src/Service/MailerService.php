<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Exception;

class MailerService
{
    private $client;
    private $logger;

    // Inyectamos el LoggerInterface en el constructor
    public function __construct(LoggerInterface $logger)
    {

        $this->logger = $logger;
    }

    public function sendLotReservedEmail($lotTitle)
    {

    }

    private function createMessage($lotTitle)
    {

    }
}