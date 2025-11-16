<?php

declare(strict_types=1);

namespace App\Diba\Doctrine\Exception;

use function assert;

use Doctrine\DBAL\Driver\AbstractException;
use function oci_error;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class Error extends AbstractException
{
    /**
     * @param resource $resource
     */
    public static function new($resource): self
    {
        $error = oci_error($resource);
        assert($error !== false);

        return new self($error['message'], null, $error['code']);
    }
}
