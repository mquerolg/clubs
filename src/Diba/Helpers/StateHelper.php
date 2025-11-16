<?php

namespace App\Diba\Helpers;

class StateHelper
{
    /**
     * Lots status
     */
    public const AVAILABLE = 1;
    public const RESERVED = 2;
    public const REQUESTED = 3;
    public const PREPARED = 4;
    public const IN_TRANSIT = 5;
    public const IN_LIBRARY = 6;
    public const IS_RETURN = 7;
    public const IS_COLLECTED = 8;
    public const IS_RETURNED = 9;
    public const FINISHED = 10;

    /**
     * Reserved status
     */
    public const IS_RESERVED = true;

    /**
     * Libraries types
     */
    public const LIBRARY_TYPE = 1;
    public const BIBLIOBUS_TYPE = 2;
}
