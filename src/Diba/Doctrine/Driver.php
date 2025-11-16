<?php

namespace App\Diba\Doctrine;

use App\Diba\Doctrine\Exception\ConnectionFailed;
use Doctrine\DBAL\Driver\AbstractOracleDriver;
use function oci_connect;

use const OCI_NO_AUTO_COMMIT;
use function oci_pconnect;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * A Doctrine DBAL driver for the Oracle OCI8 PHP extensions.
 */
final class Driver extends AbstractOracleDriver
{
    /**
     * {@inheritdoc}
     *
     * @return Connection
     */
    public function connect(array $params)
    {
        $session = new Session();

        if ($session->has('user_info')) {
            $username = $session->get('user_info')['user'];
            $password = $session->get('user_info')['pass'];
        } else {
            $username = $params['user'];
            $password = $params['password'];
        }

        $host = $params['host'] ?? '';
        $charset = $params['charset'] ?? '';
        $sessionMode = $params['sessionMode'] ?? OCI_NO_AUTO_COMMIT;

        if (!empty($params['persistent'])) {
            $connection = @oci_pconnect($username, $password, $host, $charset, $sessionMode);
        } else {
            $connection = @oci_connect($username, $password, $host, $charset, $sessionMode);
        }

        if ($connection === false) {
            throw ConnectionFailed::new();
        }

        return new Connection($connection);
    }
}
