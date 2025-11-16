<?php

/**
 * Classe encargada de formar el objeto salida Biblioteca mediante captadores
 *
 * @author Jordi Martínez <jordi.martinez@basetis.com>
 * @version 1.0
 */

namespace App\Diba;

class Biblioteca
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
     * ID bilioteca
     *
     * @var int
     */
    private $id;

    /**
     * ID global
     */
    private $global;

    /**
     * Tipo de biblioteca
     *
     * @var int
     */
    private $type;

    /**
     * Nombre biblioteca
     *
     * @var string
     */
    private $name;

    /**
     * Municipio biblioteca
     *
     * @var string
     */
    private $municipality;

    /**
     * Zona biblioteca
     *
     * @var string
     */
    private $area;

    /**
     * E-mail de contacto biblioteca
     *
     * @var string
     */
    private $email;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    /**
     * Constructor, init
     *
     * @param $raw El objeto json principal en el que trabajar
     */
    public function __construct($raw, $type = null)
    {
        $this->raw = $raw;

        if (is_null($type)) {
            $this->makeType();
        } else {
            $this->type = $type;
        }

        $this->makeId();
        $this->makeGlobal();
        $this->makeName();
        $this->makeMunicipality();
        $this->makeArea();
        $this->makeEmail();
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
        if (is_null($this->type)) {
            return [];
        }

        return [ // Article
            'id' => $this->getId(),
            'global' => $this->getGlobal(),
            'type' => $this->getType(),
            'name' => $this->getName(),
            'municipality' => $this->getMunicipality(),
            'area' => $this->getArea(),
            'email' => $this->getEmail(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GENERADORES
    |--------------------------------------------------------------------------
    */

    /**
     * Obtiene el valor email de un elemento
     */
    protected function makeEmail()
    {
        if ($this->type == 'bibliobus') {
            $this->email = $this->raw->email ?? null;
        } else {
            $this->email = $this->raw->email[0] ?? null;
        }
    }

    /**
     * Obtiene el valor area de un elemento
     */
    protected function makeArea()
    {
        if ($this->type == 'bibliobus') {
            $this->area = null;
        } else {
            $this->area = $this->raw->rel_municipis->grup_comarca->comarca_nom ?? null;
        }
    }

    /**
     * Obtiene el valor municipio de un elemento
     */
    protected function makeMunicipality()
    {
        if ($this->type == 'bibliobus') {
            $municipality = $this->raw->adreca ?? null;
            $tmp = explode(' 08', $municipality);
            $this->municipality = substr(end($tmp), 4) ?? $municipality;
        } else {
            $this->municipality = $this->raw->rel_municipis->municipi_nom ?? null;
        }
    }

    /**
     * Obtiene el valor nombre de un elemento
     */
    protected function makeName()
    {
        if ($this->type == 'bibliobus') {
            $this->name = $this->raw->nom ?? null;
        } else {
            $this->name = $this->raw->adreca_nom ?? null;
        }
    }

    /**
     * Obtiene el valor id de un elemento
     */
    protected function makeId()
    {
        if ($this->type == 'bibliobus') {
            $this->id = $this->raw->bibliobus_id ?? null;
        } else {
            $this->id = $this->raw->punt_id ?? null;
        }
    }

    /**
     * Obtiene el valor ID global de un elemento
     */
    protected function makeGlobal()
    {
        $this->global = $this->raw->id_secundari;
    }

    /**
     * Obtiene el valor tipo de un elemento
     */
    protected function makeType()
    {
        if (isset($this->raw->bibliobus_id)) {
            $this->type = 'bibliobus';
        } else {
            $this->type = 'biblioteca';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIONES DE ENTRADA Y SALIDA
    |--------------------------------------------------------------------------
    */

    /**
     * Get e-mail de contacto biblioteca
     *
     * @return  string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set e-mail de contacto biblioteca
     *
     * @param  string  $email  E-mail de contacto biblioteca
     *
     * @return  self
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get zona biblioteca
     *
     * @return  string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set zona biblioteca
     *
     * @param  string  $area  Zona biblioteca
     *
     * @return  self
     */
    public function setArea(string $area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get municipio biblioteca
     *
     * @return  string
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * Set municipio biblioteca
     *
     * @param  string  $municipality  Municipio biblioteca
     *
     * @return  self
     */
    public function setMunicipality(string $municipality)
    {
        $this->municipality = $municipality;

        return $this;
    }

    /**
     * Get nombre biblioteca
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set nombre biblioteca
     *
     * @param  string  $name  Nombre biblioteca
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get iD bilioteca
     *
     * @return  int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set iD bilioteca
     *
     * @param  int  $id  ID bilioteca
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get tipo de biblioteca
     *
     * @return  int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set tipo de biblioteca
     *
     * @param  int  $type  Tipo de biblioteca
     *
     * @return  self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get ID global
     */
    public function getGlobal()
    {
        return $this->global;
    }

    /**
     * Set ID global
     *
     * @return  self
     */
    public function setGlobal($global)
    {
        $this->global = $global;

        return $this;
    }
}
