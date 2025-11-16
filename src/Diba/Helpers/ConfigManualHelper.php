<?php

namespace App\Diba\Helpers;

class ConfigManualHelper
{
    /**
     * Get value for id parameter
     *
     * @param  string $label
     * @return string|array
     */
    public static function get(string $id)
    {
        $conn = oci_connect($_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_HOST']);

        if (!$conn) {
            return null;
        } else {
            oci_execute(oci_parse($conn, 'SET ROLE ALL'));

            $stid = oci_parse($conn, "SELECT * FROM bibl_config WHERE con_id = '" . $id . "'");

            oci_execute($stid);

            $data = [];

            oci_fetch_all($stid, $data);

            return $data['CON_VALOR'][0] ?? '';
        }
    }

    /**
     * getArray
     *
     * @param  array $ids
     * @return string|array
     */
    public static function getArray($ids)
    {
        $conn = oci_connect($_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_HOST']);

        if (!$conn) {
            return null;
        } else {
            oci_execute(oci_parse($conn, 'SET ROLE ALL'));

            $valuesList = '';
            foreach ($ids as $value) {
                $valuesList .= '\'' . $value . '\'';

                if (next($ids) == true) {
                    $valuesList .= ',';
                }
            }
            $query = 'SELECT * FROM bibl_config WHERE con_id IN (' . $valuesList . ')';

            $stid = oci_parse($conn, $query);

            oci_execute($stid);

            $data = [];

            oci_fetch_all($stid, $data);

            return $data ?? '';
        }
    }
}
