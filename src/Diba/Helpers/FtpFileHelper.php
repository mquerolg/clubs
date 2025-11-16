<?php

namespace App\Diba\Helpers;

use App\Diba\Helpers\ConfigManualHelper as Manual;

class FtpFileHelper
{
    /**
     * Send file with ftp connection
     *
     * @param  mixed $dataArray
     * @param  mixed $filename
     * @param  mixed $header
     * @return void
     */
    public static function csvFtpMaker($dataArray, $filename, $header)
    {
        if ($dataArray && is_countable($dataArray)) {
            $data = $header;

            foreach ($dataArray as $line) {
                $data .= "\n";
                $data .= $line;
            }
        } else {
            throw new \Exception('Error al crear el arxivo via ftp.');
        }

        $dataFtpArray = Manual::getArray(['ftp_server_sap', 'ftp_port_sap', 'ftp_user_sap', 'ftp_password_sap', 'ftp_directory_sap']);

        $ftp_server = $dataFtpArray['CON_VALOR'][3];
        $ftp_user_name = $dataFtpArray['CON_VALOR'][4];
        $ftp_user_pass = $dataFtpArray['CON_VALOR'][1];

        $local_file = fopen('php://temp', 'r+');

        fwrite($local_file, utf8_decode($data));
        rewind($local_file);

        $ftp_conn = ftp_connect($ftp_server);

        $login_result = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);

        if ($login_result) {
            $upload_result = ftp_fput($ftp_conn, $dataFtpArray['CON_VALOR'][0] . $filename, $local_file, FTP_ASCII);
        }

        if (!$login_result || !$upload_result) {
            throw new \Exception('Error al crear el arxivo via ftp.');
        }

        ftp_close($ftp_conn);
        fclose($local_file);
    }
}
