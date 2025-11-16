<?php

namespace App\Diba\Helpers;

class OptionsHelper
{
    /**
     * Get value for label parameter
     *
     * @param  string $label
     * @return null|string
     */
    public static function get(string $label)
    {
        $conn = oci_connect($_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_HOST']);

        if (!$conn) {
            return null;
        }

        oci_execute(oci_parse($conn, 'SET ROLE ALL'));

        $stid = oci_parse($conn, "SELECT value FROM CLUBS_OPTIONS WHERE label = '" . $label . "'");

        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            return $row['VALUE'];
        }

        return null;
    }

    /**
     * Get all values
     *
     * @return null|array
     */
    public static function getAll()
    {
        $conn = oci_connect($_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_HOST']);

        if (!$conn) {
            return null;
        }

        oci_execute(oci_parse($conn, 'SET ROLE ALL'));

        $stid = oci_parse($conn, 'SELECT * FROM CLUBS_OPTIONS');

        oci_execute($stid);

        $data = [];

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * set values parameter
     *
     * @param  string $value1 max_entry_date
     * @param  string $value2 max_return_library
     * @param  string $value3 max_return_bus
     * @param  string $value4 max_return_library_lf
     *
     * @return null|bool
     */
    public static function set(string $value1, string $value2, string $value3, string $value4)
    {
        $conn = oci_connect($_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_HOST']);

        if (!$conn) {
            return null;
        }

        oci_execute(oci_parse($conn, "SET ROLE ALL"));

        $stmt = oci_parse($conn, "UPDATE CLUBS_OPTIONS set value = :data1 where label = 'max_entry_date'");

        oci_bind_by_name($stmt, ':data1', $value1);

        $result = oci_execute($stmt);

        $stmt = oci_parse($conn, "UPDATE CLUBS_OPTIONS set value = :data2 where label = 'max_return_library'");

        oci_bind_by_name($stmt, ':data2', $value2);

        $result = oci_execute($stmt);

        $stmt = oci_parse($conn, "UPDATE CLUBS_OPTIONS set value = :data3 where label = 'max_return_bus'");

        oci_bind_by_name($stmt, ':data3', $value3);

        $result = oci_execute($stmt);

        $stmt = oci_parse($conn, "UPDATE CLUBS_OPTIONS set value = :data4 where label = 'max_return_library_lf'");

        oci_bind_by_name($stmt, ':data4', $value4);

        $result = oci_execute($stmt);

        return $result;
    }

    /**
     * Find a file in path recursive
     *
     * @param [string] $file
     * @param [string] $path
     *
     * @return bool|string
     */
    public static function findFile($file, $path)
    {
        if (!is_dir($path)) {
            return false;
        }

        $path = realpath($path);
        $files = scandir($path);

        foreach ($files as $nombreArchivo) {
            if ($nombreArchivo === '.' || $nombreArchivo === '..') {
                continue;
            }

            $ruta = $path . DIRECTORY_SEPARATOR . $nombreArchivo;

            if (is_dir($ruta)) {
                $subdirectorio = self::findFile($file, $ruta);
                if ($subdirectorio !== false) {
                    return basename($subdirectorio);
                }
            } elseif ($nombreArchivo === $file) {
                return basename($path);
            }
        }

        return false;
    }
}
