<?php

namespace App\Diba\Doctrine\Types;

use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

class SillyDateTimeType extends DateTimeType
{
    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof \DateTime) {
            return $value;
        }

        $val = \DateTime::createFromFormat('d/m/y H:i:s,u', $value);

        if ($val instanceof \DateTime) {
            return $val;
        }

        $val = \DateTime::createFromFormat('d-M-y h.i.s.u A', $value);

        if (!$val instanceof \DateTime) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $val;
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            // Dvol
            return $value->format('d-M-y h.i.s.u A');

            // Local
            // return $value->format('d/m/y h:i:s,u');
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTime']);
    }
}
