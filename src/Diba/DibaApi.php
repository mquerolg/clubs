<?php

namespace App\Diba;

class DibaApi
{
    /*
    |--------------------------------------------------------------------------
    | VARIABLES DE ENTORNO CONFIGURABLES
    |--------------------------------------------------------------------------
    */

    /**
     * DibaApi do URL
     *
     * @var string
     */

    public const DIBA_DO_URL = 'https://do.diba.cat/api/';

    /**
     * DibaApi aplicacions URL
     *
     * @var string
     */

    public const DIBA_APLICACIONS_URL = 'https://aplicacions.diba.cat/bibl_servweb/consulta_bibliografic?ws_usuari=clubs&ws_clau=clubs&tipus_resposta=json&codi=';

    /**
     * Cantidad de tiempo en segundos antes antes de dar por fallida una conexión.
     *
     * @var int
     */
    protected $connectionTimeout = 10;

    /**
     * Cantiedad de tiempo máximo, en segundos, que se permite la ejecución de cURL.
     *
     * @var int
     */
    protected $timeout = 90;

    /**
     * Resultados de la consulta a API.
     *
     * @var array
     */
    protected $result = [];

    /**
     * Contiene los elemento bibliotecas de una consulta.
     *
     * @var array
     */
    protected $bibliotecas = [];

    /**
     * Contiene los elemento book de una consulta.
     *
     * @var array
     */
    protected $book = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCIONES DE ENTRADA Y SALIDA
    |--------------------------------------------------------------------------
    */

    /**
     * Genera un array de bibliotecas a partir de los elementos dados.
     *
     * @param string|array $elements Admite la entrada de elementos por string o por array.
     *
     * @return $result Retorno de la petición en formato array.
     */
    public function find($elements)
    {
        $this->bibliotecas = [];

        if (is_array($elements)) {
            foreach ($elements as $element) {
                $this->setByLibraryId($element);
            }
        } else {
            $this->setByLibraryId($elements);
        }

        return $this->bibliotecas;
    }

    /**
     * Agrega un elemento a bibliotecas a partir de un $id dado.
     *
     * @param string $id Id del elemento a importar.
     */
    protected function setByLibraryId($id)
    {
        if (strpos($id, 'biblioteca') === false) {
            $url = DibaApi::DIBA_DO_URL . 'dataset/bibliobusos/camp-bibliobus_id/' . $id . '/format/json';
        } else {
            $url = DibaApi::DIBA_DO_URL . 'dataset/biblioteques/camp-punt_id/' . $id . '/format/json';
        }

        if ($this->sendCurl($url)) {
            $elements = $this->result->elements ?? null;

            foreach ($elements as $element) {
                $this->bibliotecas[] = new Biblioteca($element, 'biblioteca');
            }
        }
    }

    /**
     * Retorna todas las bibliotecas y bibliobuses disponibles.
     *
     * @return array Contenedor global con todas las entradas.
     */
    public function getLibraries()
    {
        $this->bibliotecas = [];

        $this->getBiblioteques();
        $this->getBibliobusos();

        return $this->bibliotecas;
    }

    /**
     * Agrega todas los elementos biblioteca al contenedor global bibliotecas
     */
    protected function getBiblioteques()
    {
        $url = DibaApi::DIBA_DO_URL . 'dataset/biblioteques/format/json';

        if ($this->sendCurl($url)) {
            $elements = $this->result->elements ?? null;

            foreach ($elements as $element) {
                $this->bibliotecas[] = new Biblioteca($element, 'biblioteca');
            }
        }
    }

    /**
     * Agrega todas los elementos book ??? al contenedor global books
     */
    public function getBookFromCode(int $code)
    {
        $url = DibaApi::DIBA_APLICACIONS_URL . $code;

        if ($this->sendCurl($url)) {
            $element = $this->result->resposta->iiirecord ?? null; //'iiirecord'
            return new Book($element, $code);
        }

        return null;
    }

    /**
     * Agrega todas los elementos bibliobuses al contenedor global bibliotecas
     */
    protected function getBibliobusos()
    {
        $url = DibaApi::DIBA_DO_URL . 'dataset/bibliobusos/format/json';

        if ($this->sendCurl($url)) {
            $elements = $this->result->elements ?? null;

            foreach ($elements as $element) {
                $this->bibliotecas[] = new Biblioteca($element, 'bibliobus');
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CURL FUNCTION
    |--------------------------------------------------------------------------
    */

    /**
     * Genera una petición get a um servidor remoto
     *
     * @param string $url Dirección donde se efectua la petición
     *
     * @return $result Retorno de la petición
     */
    protected function sendCurl(string $url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($curl);
        $error = curl_error($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($error) {
            throw new Exception($error);
        }

        if ($httpcode >= 200 && $httpcode < 400) {
            $this->result = json_decode($result);

            if (json_last_error() != JSON_ERROR_NONE) {
                $json = mb_convert_encoding($result, 'UTF-8', 'ISO-8859-1');
                $this->result = json_decode($json);
            }

            return 1;
        }

        header('HTTP/1.1 502 Bad Gateway');

        echo '{"code":"' . $httpcode . '","url","' . $url . '"}';

        exit;
    }
}
