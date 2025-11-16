<?php

namespace App\Diba;

class SamcService
{
    /**
     * notifyLotPetition
     * this function send mail when lot petition is confirmed
     *
     * @param  $lot title lot
     * @param  $numShipment number shipment
     * @param  $date date received
     * @param  $link link web cdl
     * @param  $mail mail library
     * @param  $authorship author lot
     * @param  $warehouse warehouse
     *
     * @return void
     */
    public static function notifyLotPetition($lot = null, $numShipment = null, $date = null, $link = null, $mail = null, $authorship = null, $warehouse = null, $code = null, $datareceived = null)
    {
        $replyToMail = '';
        $subject = 'Petició LOT Club de lectura confirmada ( ' . $lot . ', ' . $authorship . ', ' . $warehouse . ')';
        $title = '';

        $dateFormated = '';
        if (!is_null($date) && $date instanceof \DateTime) {
            $dateFormated = $date->format('Y-m-d');
        }
        

        $dateFdatereceivedFormatedormated = '';
        if (!is_null($datareceived) && $datareceived instanceof \DateTime) {
            $datereceivedFormated = $datareceived->format('d-m-Y');
        }

        $body = '<div>Benvolguts/des <br> Acabem de rebre una petició del
                lot (<strong><u>' . $lot . '</strong></u> ; ' . $authorship . ' ; ' . $warehouse . ') segons el calendari de trameses el rebreu amb l\'enviament
                número ' . $numShipment . ' previst el ' . $datereceivedFormated
                . '.<br>Recordeu que quan el rebeu, heu d\'accedir a CLUBS LECTURA-XBM Lots (Accés restringit de la intraBib)
                i informar de la seva recepció<br>Atentament.<br><br><span class="text-footer">Unitat de Dinamització i Serveis Bibliotecaris<br>Gerència de Serveis
                de Biblioteques. Àrea de Cultura</span><br><a href="mailto:gsb.dinamitzacio@diba.cat">gsb.dinamitzacio@diba.cat</a></div>';
        $header = '';

        SamcService::samc($body, $header, $mail, $replyToMail, $subject, $title);
       // SamcService::samc($body, $header, 'querolgm@diba.cat', $replyToMail, $subject, $title);
    }

    /**
     * notifyLotInTracking
     * this function send mail notifying that lot received soon
     *
     * @param  $lot title lot
     * @param  $date petidion date
     * @param  $mail mail library
     * @param  $authorship author lot
     * @param  $code library code
     * @param  $warehouse warehouselot
     *
     * @return void
     */
    public static function notifyLotInTracking($lot = null, $date = null, $mail = null, $authorship = null, $code = null, $warehouse = null)
    {
        $replyToMail = '';
        $subject = 'Avís rebuda de lot Club de lectura ( ' . $lot . ', ' . $authorship . ', ' . $warehouse . ')';
        $title = '';
        $dateFormated = '';

        if (!is_null($date) && $date instanceof \DateTime) {
            $dateFormated = $date->format('Y-m-d');
        }

        $body = '<div>Benvolguts/des <br> Amb el proper enviament de lot de novetats
                rebreu el lot (<strong><u>' . $lot . '</strong></u> ; ' . $authorship . ' ; ' . $warehouse
                 . ') que heu demanat en data ' . $dateFormated . '. Recordeu
                que quan tingeu els lots de CL preparats pel seu retorn a la PDL,
                heu d\'accedir a l\'aplicació Clubs de lectura e indicar quin o quins
                lots retornareu amb la propera tramesa de lot de novetats.<br>D\'aquesta
                forma facilitem la tria a d\'altres biblioteques de la XBM.<br>Podeu comunicar
                qualsevol incidència a <a href="mailto:gsb.dinamitzacio@diba.cat">gsb.dinamitzacio@diba.cat</a><br>Atentament.
                <br><br><span class="text-footer">Unitat de Dinamització i Serveis Bibliotecaris<br>Gerència de Serveis de Biblioteques. Àrea de Cultura</span><br>
                <a href="mailto:gsb.dinamitzacio@diba.cat">gsb.dinamitzacio@diba.cat</a></div>';

        $header = '';

       SamcService::samc($body, $header, $mail, $replyToMail, $subject, $title);
       // SamcService::samc($body, $header, 'querolgm@diba.cat', $replyToMail, $subject, $title);
    }

    /**
     * reminderLotsReturn
     * this function send mail for reminder return lots
     *
     * @param  $lot title lot
     * @param  $mail mail library
     * @param  $authorship author lot
     * @param  $code library code
     *
     * @return void
     */
   
    public static function reminderLotsReturn($lot = null, $mail = null, $code = null, $authorship = null, $warehouse = null, $titulo = null)
    {
        $replyToMail = '';

        $subject = 'Recordatori retorn lots CL';
        $title = '';

        $body = '<div>Benvolguts/des <br> Hem detectat que el lot (<strong><u>' . $titulo . '</strong></u> ; ' . $authorship . ' ; ' . $warehouse . ')
                , s\'hauria d\'haver retornat fa 15 dies. Recordeu que heu d\'intentar retornar-ho lo abans possible per poder facilitar la
                seva disponibilitat per d\'altres biblioteques de la XBM. Si pel que sigui l\'heu de tenir més temps, si us plau, aviseu a la
                Unitat de Dinamització i Serveis Bibliotecaris (<a href="mailto:gsb.dinamitzacio@diba.cat">gsb.dinamitzacio@diba.cat</a>).<br>
                <br>Atentament<br><br><span class="text-footer">Unitat de Dinamització i Serveis Bibliotecaris<br>Gerència de Serveis de Biblioteques. Àrea de Cultura</span><br>
                <a href="mailto:gsb.dinamitzacio@diba.cat">gsb.dinamitzacio@diba.cat</a></div>';

        $header = '';
       
        SamcService::samc($body, $header, $mail, $replyToMail, $subject, $title);
       // SamcService::samc($body, $header, 'querolgm@diba.cat', $replyToMail, $subject, $title);
    }

    /**
     * notifyAdminLotInTracking
     * this function notifying lot is received
     *
     * @param  $lot title lot
     * @param  $mail library mail
     *
     * @return void
     */
    public static function notifyAdminLotInTracking($lot = null, $autor = null, $warehouse = null, $mail = null)
    {
        $replyToMail = '';
        $subject = 'Avís rebuda lot';
        $title = '';

        $body = '<div>Benvolguts/des <br><br> Hem rebut a la PDL el lot (<strong><u>' . $lot . '</u></strong> ; '. $autor.' ; '.$warehouse.' ) que us vam prestar i del qual no ens heu informat del seu retorn. <br><br>
                Recordeu que quan tingueu els lots de CL preparats pel seu retorn a la
                PDL, heu d\'accedir a l\'eina CL-XBMlots i indicar quin o quins
                lots retornareu amb la propera tramesa de lot de novetats.<br><br>D\'aquesta
                forma facilitem la tria a d\'altres biblioteques de la XBM.<br><br>Atentament.<br>
                
                <span class="text-footer">Unitat de Dinamització i Serveis Bibliotecaris<br>Gerència de Serveis de Biblioteques. Àrea de Cultura</span><br>
                <a href="mailto:gsb.dinamitzacio@diba.cat">gsb.dinamitzacio@diba.cat</a></div>'; 

        $header = '';

        SamcService::samc($body, $header, $mail, $replyToMail, $subject, $title);
        //SamcService::samc($body, $header, 'querolgm@diba.cat', $replyToMail, $subject, $title);
    }

    /**
     * cronMessage
     * this function send mail with periodicity notifying lots petitions
     *
     * @param  $data data to fill mail info
     *
     * @return void
     */
    public static function cronMessage($data)
    {
        $mail = ['gsb.gestio.udm@diba.cat', 'gsb.dinamitzacio@diba.cat'];

        $currentDate = new \DateTime();

        $replyToMail = '';
        $subject = 'Peticions lots CL del dia ' . $currentDate->format('Y-m-d');
        $title = '';

        $body = '<div>Benvolguts/des <br> El dia ' . $currentDate->format('Y-m-d') . ' s\'han rebut ' . count($data) . ' peticions de CL:
            <br>
            <br>
            <table style="border: 1px black solid;">
            <head>
                <tr>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Data</th>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Autoria</th>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Titol</th>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Codi magatzem</th>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Gènere</th>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Codi</th>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Ruta</th>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Municipi</th>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Biblioteca</th>
                    <th style="border: 1px black solid;font-size:12px;text-align:center;">Numero d\'enviament</th>
                </tr>
            </thead>
            <tbody>
            ';
        foreach ($data as $key => $value) {
            $body .= '
                <tr>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getCreatedAt()->format('Y-m-d') . '</td>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getAuthorship() . '</td>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getLot()->getTitle() . '</td>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getLot()->getWarehouse() . '</td>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getGenre() . '</td>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getLibrary()->getCode() . '</td>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getLibrary()->getLocalization()->getRoute() . '</td>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getMunicipality() . '</td>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getLibrary() . '</td>
                    <td style="border: 1px black solid;font-size:12px;text-align:center;">' . $value->getSendId() . '</td>
                </tr>';
        }

        $body .= '</tbody>
                </table>
                <br>Atentament.
                <br><br><span class="text-footer">Unitat de Dinamització i Serveis Bibliotecaris<br>Gerència de Serveis de Biblioteques. Àrea de Cultura</span><br>
                <a href="mailto:gsb.dinamitzacio@diba.cat">gsb.dinamitzacio@diba.cat</a></div>';

        $header = '';

        SamcService::samc($body, $header, $mail, $replyToMail, $subject, $title);
    }

    /**
     * notifyAdminLotInTracking
     * this function notifying lot is received
     *
     * @param  $lot title lot
     * @param  $mail library mail
     *
     * @return void
     */
    public static function notifyAdminLotReserved($data)
    {
        $replyToMail = '';
        $subject = 'Avís de disponibilitat del lot reservat';
        $title = '';
        $mail = 'gsb.dinamitzacio@diba.cat';
        
        $body = 'Ja està disponible el lot (' . $data["TITLE"] . ',' . $data["AUTHORSHIP"] . ',' . $data["WAREHOUSE"] . ') que va ser reservat <br>Atentament,
                <br>Unitat de Dinamització i Serveis<br></div>';

        $header = '';

        SamcService::samc($body, $header, $mail, $replyToMail, $subject, $title);
    }

    /**
     * notifyAdminLotChangeStatus
     * This function logs the status change and sends an email notification when a 
     * lot changes status from 1 to 2.
     *
     * @param  string $lotTitle Title of the lot
     * @param  string $authorship Author of the lot
     * @param  string $warehouseCode Warehouse code of the lot
     * @param  int $newStatusId New status ID of the lot
     *
     * @return void
     */

    public static function notifyAdminLotChangeStatus($data)
    {
        error_log("notifyAdminLotChangeStatus ejecutado" . PHP_EOL, 3, __DIR__ . '/../../var/log/dev.log');

        // Log the status change in dev.log
        $logMessage = sprintf(
            "[%s] Lot status changed: Title='%s', Author='%s', Warehouse='%s' ",
            (new \DateTime())->format('Y-m-d H:i:s'),
            $data["TITLE"], // Título del lote
            $data["AUTHORSHIP"], // Autor del lote
            $data["WAREHOUSE"] // Código del almacén
        );
        error_log($logMessage . PHP_EOL, 3, __DIR__ . '/../../var/log/dev.log');
     
        // Prepare email details
        $replyToMail = '';
        $subject = 'Avís de canvi d\'estat del lot';
        $title = '';
        $mail = 'gsb.dinamitzacio@diba.cat';
     
        $body = '<div>Benvolguts/des <br><br> Hem rebut a la PDL el lot, <strong>' . $data["TITLE"] . '</strong> (' . $data["AUTHORSHIP"] . ', ' . $data["WAREHOUSE"] . ') que vau reservar. <br><br>
                Atentament.<br>
                <span class="text-footer">Unitat de Dinamització i Serveis Bibliotecaris<br>Gerència de Serveis de Biblioteques. Àrea de Cultura</span><br>
                <a href="mailto:gsb.dinamitzacio@diba.cat">gsb.dinamitzacio@diba.cat</a></div>';
     
        $header = '';
     
        // Send the email using the samc method
        SamcService::samc($body, $header, $mail, $replyToMail, $subject, $title);
    }


    /**
     * samc
     * this function provides a template for sending mails
     *
     * @param  $body mail body
     * @param  $header header mail
     * @param  $mail mail to send mail
     * @param  $replyToMail cc of mail
     * @param  $subject mail subject
     * @param  $title mail title
     *
     * @return void
     */
    public function samc($body = null, $header = null, $mail = null, $replyToMail = null, $subject = null, $title = null)
    {
        $SAMCurl = 'https://iasprd.diba.cat:7778/sam/samWS';

        $xml_data = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cat="http://cat.diba.sam.ws/">
                                    <soapenv:Header/>
                                    <soapenv:Body>
                                    <cat:enviarTramesa>
                                    <cat:assumpte>' . $subject . ' ';
        $xml_data .= '</cat:assumpte>

        <cat:contingut> <![CDATA[
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd>
        <html xmlns=http://www.w3.org/1999/xhtml xml:lang="ca" lang="ca">
        <head>
        <title>Novetats</title>

        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
        <meta name="title" content=' . $title . ' />
        <meta name="DC.title" content=' . $title . ' />
        <meta name="description" content="" />
        <meta name="DC.description" content="" />
        <meta name="Author" content="Diputaci&oacute; de Barcelona" />
        <meta name="language" content="ca" />
        <meta name="keywords" content="" />
        <meta name="DC.keywords" content="" />
        <meta name="robots" content="index,follow" />
        <meta name="revisit" content="15 days" />
        <STYLE type=text/css>
            body {
                font-family: "Calibri", "Arial", sans-serif;
                font-size: 11px;
            }
            .text {
                font-family: "Calibri", "Arial", sans-serif;
                font-size: 11px;
                font-style: normal;
                color: #666666;
                text-decoration: none;
            }
            .text a{
                text-decoration:none;
                color:#AE5700;
            }
            .text a:hover{
                text-decoration:none;
                color:#515151;
            }
            .text1 {
                font-family: Arial;
                font-size: 13px;
                font-style: normal;
                color: #666666;
                text-decoration: none;
            .text2 {
                font-family: Verdana, Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: #891536;
            }
            .text3 {
                font-family: Verdana, Arial, Helvetica, sans-serif;
                font-size: 10px;
                color: #891536;
            }
            .text-footer {
                color:#9d2235;
            }
            h2 {
                color:#505050;
                font-family:"Trebuchet MS",Arial,Helvetica,sans-serif;
                font-size:150%;
                margin-bottom:8px;
                width:auto;
            }
            h3 {
                color:#505050;
                font-family:"Trebuchet MS",Arial,Helvetica,sans-serif;
                font-size:110%;
                margin-top: 0;
                margin-left: 0;
                margin-bottom : 8px;
                margin-right : 0;
            }
        </STYLE>
    </head>
    <body>
        ' . $body . '
    </body>
    </html>
        ]]>

        </cat:contingut>
        ';

        if (is_countable($mail) && count($mail) > 0) {
            foreach ($mail as $mailValue) {
                $xml_data .= '<cat:llistaDestinataris><cat:email>' . $mailValue . '</cat:email>
                                <cat:nom>' . $mailValue . '</cat:nom>
                                </cat:llistaDestinataris>';
            }
        } else {
            $xml_data .= '<cat:llistaDestinataris>
                            <cat:email>' . $mail . '</cat:email>
                            <cat:nom>' . $mail . '</cat:nom>
                            </cat:llistaDestinataris>';
        }

        $xml_data .= '<cat:fromNom>' . $title . '</cat:fromNom>';

        $xml_data .= '
            <cat:replyTo>
            <cat:email>' . $replyToMail . '</cat:email>
            <cat:nom>' . $replyToMail . '</cat:nom>
            </cat:replyTo>
        ';

        $xml_data .= '
        <cat:encoding>ISO-8859-1</cat:encoding>
        <cat:format>text/html</cat:format>
        <cat:aplicacio>CLUBS</cat:aplicacio>
        <cat:usuari>CLUBS</cat:usuari>
        <cat:password>xXbn67QfG</cat:password>
        </cat:enviarTramesa>
        </soapenv:Body>
        </soapenv:Envelope>';

        $ch = curl_init($SAMCurl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
    }
}
