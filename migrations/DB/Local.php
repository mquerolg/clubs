<?php

class Local
{
    private $db;

    public function __construct($servername, $database, $username, $password)
    {
        $this->db = new mysqli(
            $servername,
            $username,
            $password,
            $database
        );
    }

    public function getLots()
    {
        $query = "SELECT 
                    l.autor AS 'authorship',
                    l.titulo AS 'title',
                    l.descripcion AS 'description',
                    l.cantidad_es AS 'lang_es',
                    l.cantidad_cat AS 'lang_cat',
                    l.cantidad AS 'lang_others',
                    l.anyo AS 'year',
                    l.codi_magatzem AS 'warehouse',
                    l.activo AS 'active',
                    CASE WHEN l.disponible = 1 THEN 1 ELSE 5 END AS 'status_id',
                    l.baja AS 'deleted_at',
                    CASE WHEN l.fecha_alta IS NULL THEN FROM_UNIXTIME(l.fecha_mod) ELSE FROM_UNIXTIME(l.fecha_alta) END AS 'created_at',
                    FROM_UNIXTIME(l.fecha_mod) AS 'updated_at',
                    g.nombre AS 'genre',
                    b.email AS 'email',
                    b.email2 AS 'code'
                  FROM lotes l 
                  LEFT JOIN genero g ON g.id_genero = l.id_genero
                  LEFT JOIN biblioteca b ON b.id_biblioteca = l.id_biblioteca";

        $result = $this->db->query($query);

        $lots = [];

        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                $row[$key] = empty($row[$key]) ? null : trim($value);
            }

            $lots[] = $row;
        }

        return $lots;
    }

    public function getClubs()
    {
        $query = "SELECT 
                    b.email AS 'email',
                    b.email2 AS 'code',
                    b.descripcion AS 'observations', 
                    c.nombre AS 'name',
                    c.descripcion AS 'description',
                    c.any_creacion AS 'year',
                    c.activo AS 'active',
                    CASE WHEN c.fecha_alta IS NULL THEN FROM_UNIXTIME(c.fecha_mod) ELSE FROM_UNIXTIME(c.fecha_alta) END AS 'created_at',
                    FROM_UNIXTIME(c.fecha_mod) AS 'updated_at',
                    c.baja AS 'deleted_at'
                  FROM clubs c 
                  LEFT JOIN biblioteca b ON c.id_biblioteca = b.id_biblioteca";

        $result = $this->db->query($query);

        $clubs = [];

        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                $row[$key] = empty($row[$key]) ? null : trim($value);
            }

            $clubs[] = $row;
        }

        return $clubs;
    }

    public function getGenres()
    {
        $query = "SELECT 
                    g.nombre AS 'name',
                    g.activo AS 'active',
                    FROM_UNIXTIME(g.fecha_alta) AS 'created_at',
                    FROM_UNIXTIME(g.fecha_mod) AS 'updated_at',
                    CASE WHEN g.baja = 1 THEN CURRENT_TIMESTAMP ELSE NULL END AS 'deleted_at'
                  FROM genero g";

        $result = $this->db->query($query);

        $genres = [];

        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                $row[$key] = empty($row[$key]) ? null : trim($value);
            }

            $genres[] = $row;
        }

        return $genres;
    }

    public function getHistoric()
    {
        $query = "SELECT 
                    FROM_UNIXTIME(g.fecha_alta) as 'alta',
                    p.nombre as periodo,
                    p.id_periodo as 'periodo_id',
                    FROM_UNIXTIME(p.inicio) AS initial,
                    FROM_UNIXTIME(p.fin) AS endend,
                    b.email AS 'email',
                    b.email2 AS 'code',
                    l.autor AS 'authorship',
                    l.titulo AS 'title',
                    l.cantidad_es AS 'lang_es',
                    l.cantidad_cat AS 'lang_cat',
                    l.cantidad AS 'lang_others',
                    l.anyo AS 'lyear',
                    l.codi_magatzem AS 'warehouse',
                    l.activo AS 'lactive',
                    FROM_UNIXTIME(l.fecha_alta) AS 'created_at',
                    FROM_UNIXTIME(l.fecha_mod) AS 'updated_at',
                    c.nombre AS 'name',
                    c.any_creacion AS 'year',
                    c.activo AS 'active'
				FROM graella  AS g
				INNER JOIN lotes AS l ON l.id_lotes=g.titulo
				INNER JOIN clubs AS c ON c.id_clubs=g.club
				INNER JOIN biblioteca AS b ON b.id_biblioteca=c.id_biblioteca
                INNER JOIN periodo p ON p.id_periodo = g.periodo
                WHERE c.baja = 0 AND l.baja = 0 AND b.baja = 0";

        $result = $this->db->query($query);

        $historic = [];

        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                $row[$key] = empty($row[$key]) ? null : trim($value);
            }

            $historic[] = $row;
        }

        return $historic;
    }
}
