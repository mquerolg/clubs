<?php

/**
 * Classe encargada de formar el objeto salida Article mediante captadores XML
 *
 * @author Jordi Martínez <jordi.martinez@basetis.com>
 * @version 1.0
 */

namespace App\Diba;

use PDO;

class Book
{
    /*
    |--------------------------------------------------------------------------
    | VARIABLES DE ENTORNO
    |--------------------------------------------------------------------------
    */

    /**
     * Contenedor json
     *
     * @var object
     */
    private $raw;

    /**
     * Codi Exemplar (Codi ID API) del llibre
     *
     * @var int
     */
    private $code;

    /**
     * Codi Bibliografic del llibre
     *
     *  @var float|null
     */
    private $codiBiblio;

    /**
     * Autoria del llibre
     *
     * @var string|null
     */
    private $autoria;

    /**
     * Titol del llibre
     *
     * @var string|null
     */
    private $titol;

    /**
     * Idioma del llibre
     *
     * @var string|null
     */
    private $idioma;

    /**
     * Signatura del llibre
     *
     * @var string|null
     */
    private $signatura;

    /**
     * Classificacio del llibre
     *
     * @var string|null
     */
    private $classificacio;

    /**
     * Publicacio del llibre (Lloc, editorial i any)
     *
     * @var string|null
     */
    private $publicacio;

    /**
     * Col.leccio del llibre
     *
     * @var string|null
     */
    private $colleccio;

    /**
     * ISBN del llibre
     *
     * @var int|null
     */
    private $isbn;

    /**
     * Descripció física del llibre (num. pagines, altura)
     *
     * @var string|null
     */
    private $descripcioFisica;

    /**
     * Contiene los codigos de los ejemplares
     *
     * @var array
     */
    protected $codes = [];

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    /**
     * Constructor, init
     *
     * @param $raw El objeto json principal en el que trabajar
     * @param $code El int del codigo de libro a consultar
     */
    public function __construct($raw, $code)
    {
        $this->raw = $raw;
        $this->code = $code;

        $this->makeCodiBiblio();
        $this->makeAutoria();
        $this->makeTitol();
        $this->makeIdioma();
        $this->makeSignatura();
        $this->makePublicacio();
        $this->makeColleccio();
        $this->makeClassificacio();
        $this->makeISBN();
        $this->makeDescripcioFisica();
        $this->makeBarcodes();
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS
    |--------------------------------------------------------------------------
    */

    /**
     * Metodo mágico
     *
     * @return string retorno del objecto print_r para depurar
     */
    public function __toString()
    {
        return print_r($this->raw, true);
    }

    /**
     * Ejecute todos los captadores en el objeto
     *
     * @return array array con todos los captadores
     */
    public function toArray()
    {
        if (is_null($this->code)) {
            return [];
        }

        return [ // Article
            'code' => $this->getCode(),
            'codi bibliografic' => $this->getCodiBiblio(),
            'isbn' => $this->getIsbn(),
            'autoria' => $this->getAutoria(),
            'titol' => $this->getTitol(),
            'idioma' => $this->getIdioma(),
            'signatura' => $this->getSignatura(),
            'classificacio' => $this->getClassificacio(),
            'publicacio' => $this->getPublicacio(),
            'colleccio' => $this->getColleccio(),
            'descripcio fisica' => $this->getDescripcioFisica(),
            'codes' => $this->getCodes(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GENERADORES
    |--------------------------------------------------------------------------
    */

    /**
     * Obtiene el valor codi bibliografic de un elemento
     */
    protected function makeCodiBiblio()
    {
        $codiBiblio = $this->raw->recordinfo->recordkey ?? null;
        $codiBiblio = $this->convertToLongForm($codiBiblio);

        $this->codiBiblio = $codiBiblio;
    }

    protected function isLongType($str)
    {
        // Verifica si la cadena empieza con "b" y termina con un número o "x".
        return preg_match('/^b[0-9]+[0-9x]$/', $str) ? true : false;
    }

    protected function calculateDigitControl($str)
    {
        $suma = 0;
        $strlen = strlen($str);

        for ($i = 0; $i < $strlen; $i++) {
            $char = substr($str, $i, 1);
            $suma += $char * ($strlen + 1 - $i);
        }
        $resto = $suma % 11;
        $digitoControl = ($resto == 10) ? 'x' : $resto;
        
        return $digitoControl;
    }

    protected function convertToLongForm($str)
    {
        if ($this->isLongType($str)) {
            $str = substr($str, 1);
        }
        // Agregar 'b' al inicio y el dígito de control al final
        return 'b' . $str . $this->calculateDigitControl($str);
    }


    /**
     * Obtiene el valor autoria de un elemento
     */
    protected function makeAutoria()
    {
        // si hi ha autor: marctag=100, sino autor secundari marctag=700
        $varflds = $this->raw->varfld ?? null;
        $this->autoria = '';

        foreach ((array) $varflds as $varfld) {
            if ($varfld->marcinfo->marctag == 100 && isset($varfld->marcsubfld) && !empty($varfld->marcsubfld)) {
                if (is_countable($varfld->marcsubfld)) {
                    $this->autoria = $varfld->marcsubfld[0]->subfielddata ?? '';
                } else {
                    $this->autoria = $varfld->marcsubfld->subfielddata ?? '';
                }
                break;
            } elseif ($varfld->marcinfo->marctag == '700') {
                if (is_countable($varfld->marcsubfld)) {
                    $this->autoria = $varfld->marcsubfld[0]->subfielddata ?? '';
                } else {
                    $this->autoria = $varfld->marcsubfld->subfielddata ?? '';
                }
                break;
            }
        }
    }

    /**
     * Obtiene el valor titulo de un elemento
     */
    protected function makeTitol()  // usar la contrabarra como indicador del fin
    {
        $varflds = $this->raw->varfld ?? null;
        $title = '';

        foreach ((array) $varflds as $varfld) {
            if ($varfld->header->tag == 'TITLE') {
                $rawTitol = $varfld->marcsubfld;

                foreach ((array) $rawTitol as $partialTitol) {
                    $title = $title . $partialTitol->subfielddata . ' ';
                }

                $this->titol = explode(' /', $title)[0];
            }
        }
    }

    /**
     * Obtiene el valor idioma de un elemento
     */
    protected function makeIdioma() // array con idiomas y num de ejemplares
    {
        $idiomaArray = [];
        $idiomesRaw = $this->raw->typeinfo ?? null;

        foreach ((array) $idiomesRaw as $language) {
            $idiomaArray[ $language[0]->fixvalue ] = $language[0]->fixnumber;
        }

        $this->idioma = $idiomaArray;
    }

    /**
     * Obtiene el valor signatura de un elemento
     */
    protected function makeSignatura()
    {
        $varflds = $this->raw->varfld ?? null;
        $this->signatura = null;

        foreach ((array) $varflds as $varfld) {
            if ($varfld->header->tag == 'CALL #') {
                $this->signatura = $varfld->marcsubfld->subfielddata ?? null;
                break;
            }
        }
    }

    /**
     * Obtiene el valor classificacio de un elemento
     */
    protected function makeClassificacio() //revisar el email, crec que s'han equivocat
    {
        $varflds = $this->raw->varfld ?? null;
        $this->classificacio = null;

        foreach ((array) $varflds as $varfld) {
            if ($varfld->header->tag == 'CDU') {
                $this->classificacio = $varfld->marcsubfld->subfielddata ?? null;
                break;
            }
        }
    }

    /**
     * Obtiene el valor publicacio de un elemento
     */
    protected function makePublicacio()
    {
        $varflds = $this->raw->varfld ?? null;
        $this->publicacio = '';

        foreach ((array) $varflds as $varfld) {
            if ($varfld->header->tag == 'PUB INFO') {
                $rawPublicacio = $varfld->marcsubfld;

                foreach ((array) $rawPublicacio as $partialPublicacio) {
                    if (is_array($partialPublicacio->subfielddata)) {
                        $this->publicacio = $this->publicacio . ($partialPublicacio->subfielddata[0] ?? '');
                    } else {
                        $this->publicacio = $this->publicacio . ($partialPublicacio->subfielddata ?? '');
                    }
                }
            }
        }
    }

    /**
     * Obtiene el valor coleccion de un elemento
     */
    protected function makeColleccio()
    {
        $varflds = $this->raw->varfld ?? null;
        $this->colleccio = null;

        foreach ((array) $varflds as $varfld) {
            if ($varfld->marcinfo->marctag == '830') {
                $this->colleccio = is_array($varfld->marcsubfld) ?
                        $varfld->marcsubfld[0]->subfielddata
                    :
                        $varfld->marcsubfld->subfielddata
                ;

                break;
            }
        }
    }

    /**
     * Obtiene el valor ISBN de un elemento
     */
    protected function makeISBN()
    {
        $varflds = $this->raw->varfld ?? null;
        $this->isbn = null;

        foreach ((array) $varflds as $varfld) {
            if ($varfld->marcinfo->marctag == '020') {
                $this->isbn = $varfld->marcsubfld->subfielddata;
                break;
            }
        }
    }

    /**
     * Obtiene el valor Descripcio Fisica de un elemento
     */
    protected function makeDescripcioFisica()
    {
        $varflds = $this->raw->varfld ?? null;
        $this->descripcioFisica = null;

        foreach ((array) $varflds as $varfld) {
            if ($varfld->marcinfo->marctag == '300') {
                foreach ((array) $varfld->marcsubfld as $partialDescripcioFisica) {
                    $this->descripcioFisica = $this->descripcioFisica . $partialDescripcioFisica->subfielddata;
                }
                break;
            }
        }
    }

    /**
     * Obtiene el valor de los códigos bibliograficos
     */
    protected function makeBarcodes()
    {
        $code = substr($this->codiBiblio, 1, -1);

        if (empty($code)) {
            return;
        }

        // Información de la base de datos
        $host = $_ENV['DB_SIERRA_HOST'];
        $db = $_ENV['DB_SIERRA_DB'];
        $user = $_ENV['DB_SIERRA_USER'];
        $pass = $_ENV['DB_SIERRA_PASS'];
        $port = $_ENV['DB_SIERRA_PORT'];

        // Opciones de PDO
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // Conexión a la base de datos
        $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }

        // Consulta SQL
        $sql = "
            SELECT
                DISTINCT i.barcode AS BARCODE
            FROM
                sierra_view.item_view i,
                sierra_view.bib_view b,
                sierra_view.bib_record_item_record_link l
            WHERE
                l.item_record_id = i.id
                AND l.bib_record_id = b.id
                AND i.location_code = '00019'
                AND b.record_num = :num
        ";

        // Preparar y ejecutar consulta
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['num' => $code]);

        // Recuperar resultados
        $this->codes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIONES DE ENTRADA Y SALIDA
    |--------------------------------------------------------------------------
    */

    /**
     * Get example bar code of the book
     *
     * @return  int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get codi bibliografic of the book
     *
     * @return  float
     */
    public function getCodiBiblio()
    {
        return $this->codiBiblio;
    }

    /**
     * Set codi bibliografic of the book
     *
     * @param  float  $codiBiblio Codi bibliografic of the book
     *
     * @return  self
     */
    public function setCodiBiblio(string $codiBiblio)
    {
        $this->codiBiblio = $codiBiblio;

        return $this;
    }

    /**
     * Get autoria of the book
     *
     * @return  string
     */
    public function getAutoria()
    {
        return $this->autoria;
    }

    /**
     * Set autoria of the book
     *
     * @param  string  $autoria Autoria of the book
     *
     * @return  self
     */
    public function setAutoria(string $autoria)
    {
        $this->autoria = $autoria;

        return $this;
    }

    /**
     * Get titol of the book
     *
     * @return  string
     */
    public function getTitol()
    {
        return $this->titol;
    }

    /**
     * Set titol of the book
     *
     * @param  string  $titol Titol of the book
     *
     * @return  self
     */
    public function setTitol(string $titol)
    {
        $this->titol = $titol;

        return $this;
    }

    /**
     * Get idioma of the book
     *
     * @return  string
     */
    public function getIdioma()
    {
        return $this->idioma;
    }

    /**
     * Set idioma of the book
     *
     * @param  string  $idioma Idioma of the book //return param array?? ['foo'=>'bar']
     *
     * @return  self
     */
    public function setIdioma(string $idioma)
    {
        $this->idioma = $idioma;

        return $this;
    }

    /**
     * Get signatura of the book
     *
     * @return  string
     */
    public function getSignatura()
    {
        return $this->signatura;
    }

    /**
     * Set signatura of the book
     *
     * @param  string  $signatura Signatura of the book
     *
     * @return  self
     */
    public function setSignatura(string $signatura)
    {
        $this->signatura = $signatura;

        return $this;
    }

    /**
     * Get classificacio of the book
     *
     * @return  string
     */
    public function getClassificacio()
    {
        return $this->classificacio;
    }

    /**
     * Set classificacio of the book
     *
     * @param  string  $classificacio Classificacio of the book
     *
     * @return  self
     */
    public function setClassificacio(string $classificacio)
    {
        $this->classificacio = $classificacio;

        return $this;
    }

    /**
     * Get publicacio of the book
     *
     * @return  string
     */
    public function getPublicacio()
    {
        return $this->publicacio;
    }

    /**
     * Set publicacio of the book
     *
     * @param  string  $publicacio Publicacio of the book
     *
     * @return  self
     */
    public function setPublicacio(string $publicacio)
    {
        $this->publicacio = $publicacio;

        return $this;
    }

    /**
     * Get colleccio of the book
     *
     * @return  string
     */
    public function getColleccio()
    {
        return $this->colleccio;
    }

    /**
     * Set colleccio of the book
     *
     * @param  string  $colleccio Colleccio of the book
     *
     * @return  self
     */
    public function setColleccio(string $colleccio)
    {
        $this->colleccio = $colleccio;

        return $this;
    }

    /**
     * Get ISBN of the book
     *
     * @return  int
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * Set ISBN of the book
     *
     * @param  int  $isbn ISBN of the book
     *
     * @return  self
     */
    public function setIsbn(int $isbn)
    {
        $this->isbn = $isbn;

        return $this;
    }

    /**
     * Get Descripcio fisica of the book
     *
     * @return  string
     */
    public function getDescripcioFisica()
    {
        return $this->descripcioFisica;
    }

    /**
     * Set Descripcio fisica  of the book
     *
     * @param  string  $descripcioFisica Descripcio fisica  of the book
     *
     * @return  self
     */
    public function setDescripcioFisica(string $descripcioFisica)
    {
        $this->descripcioFisica = $descripcioFisica;

        return $this;
    }

    /**
     * Get contiene los codigos de los ejemplares
     *
     * @return  array
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * Set contiene los codigos de los ejemplares
     *
     * @param  array  $codes  Contiene los codigos de los ejemplares
     *
     * @return  self
     */
    public function setCodes(array $codes)
    {
        $this->codes = $codes;

        return $this;
    }
}
