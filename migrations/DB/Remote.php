<?php

class Remote
{
    private $db;
    private $genres = [];
    private $error = '';

    public function __construct($host, $username, $password)
    {
        $this->db = oci_connect($username, $password, $host);

        oci_execute(oci_parse($this->db, 'SET ROLE ALL'));
    }

    public function query($query)
    {
        $stid = oci_parse($this->db, $query);

        oci_execute($stid);

        return $stid;
    }

    public function setSerials(): void
    {
        echo "
        create or replace
        procedure clubs_reset_seq( p_seq_name in varchar2, p_table in varchar2 )
        is
            l_val number;
            c_val number;
        begin
            execute immediate
            'select ' || p_seq_name || '.nextval from dual' INTO l_val;
            
            execute immediate
            'SELECT id FROM ' || p_table || ' WHERE rownum <= 1 ORDER BY ID DESC' INTO c_val;
        
            execute immediate
            'alter sequence ' || p_seq_name || ' increment by ' || (c_val - l_val) || ' minvalue 0';
        
            execute immediate
            'select ' || p_seq_name || '.nextval from dual' INTO l_val;
        
            execute immediate
            'alter sequence ' || p_seq_name || ' increment by 1 minvalue 0';
        end;
        \n";

        echo "
        BEGIN
            clubs_reset_seq ('BIBL_CLUBS_CLUBS_SEQ','CLUBS_CLUBS');
            COMMIT;
            clubs_reset_seq ('BIBL_CLUBS_GENRES_SEQ','CLUBS_GENRES');
            COMMIT;
            clubs_reset_seq ('BIBL_CLUBS_HISTORIC_SEQ','CLUBS_HISTORIC');
            COMMIT;
            clubs_reset_seq ('BIBL_CLUBS_LIBRARIES_SEQ','CLUBS_LIBRARIES');
            COMMIT;
            clubs_reset_seq ('BIBL_CLUBS_LOTS_SEQ','CLUBS_LOTS');
            COMMIT;
        END;
        \n";

        echo "DROP PROCEDURE clubs_reset_seq; \n";
    }

    public function setLibraries(): void
    {
        $and = ',';

        echo 'TRUNCATE TABLE CLUBS_LIBRARIES;' . "\n";

        $stid = oci_parse($this->db, 'SELECT * FROM CLUBS_LIBRARIES');

        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $insert = 'INSERT INTO CLUBS_LIBRARIES (ID,ID_DIBA,ACTIVE,NAME,MUNICIPALITY,EMAIL,CODE,ZONE,OBSERVATIONS,TYPE) VALUES (';
            $insert .= $row['ID'] . $and;
            $insert .= "'" . $row['ID_DIBA'] . "'" . $and;
            $insert .= $row['ACTIVE'] . $and;
            $insert .= "'" . $this->scapeCharacters($row['NAME']) . "'" . $and;
            $insert .= "'" . $this->scapeCharacters($row['MUNICIPALITY']) . "'" . $and;
            $insert .= "'" . $this->scapeCharacters($row['EMAIL']) . "'" . $and;
            $insert .= "'" . $this->scapeCharacters($row['CODE']) . "'" . $and;
            $insert .= "'" . $this->scapeCharacters($row['ZONE']) . "'" . $and;
            $insert .= "'" . $this->scapeCharacters($row['OBSERVATIONS']) . "'" . $and;
            $insert .= $row['TYPE'];
            $insert .= ');';

            echo $insert . "\n";
        }

        echo 'UPDATE CLUBS_LIBRARIES SET ACTIVE = 0 WHERE MUNICIPALITY = \'Barcelona\';' . "\n";
    }

    public function setGenres($genres): void
    {
        $and = ',';

        echo 'TRUNCATE TABLE CLUBS_GENRES;' . "\n";

        foreach ($genres as $key => $genre) {
            if (!empty($genre['name'])) {
                $insert = 'INSERT INTO CLUBS_GENRES (ID,NAME,ACTIVE,CREATED_AT,UPDATED_AT,DELETED_AT) VALUES (';
                $insert .= ($key + 1) . $and;
                $insert .= "'" . $this->scapeCharacters($genre['name']) . "'" . $and;
                $insert .= $genre['active'] . $and;
                $insert .= !empty($genre['created_at']) ? "TO_TIMESTAMP('" . $genre['created_at'] . "','yyyy-mm-dd HH24:MI:SS.FF')" : 'CURRENT_TIMESTAMP';
                $insert .= $and;
                $insert .= !empty($genre['updated_at']) ? "TO_TIMESTAMP('" . $genre['updated_at'] . "','yyyy-mm-dd HH24:MI:SS.FF')" : 'CURRENT_TIMESTAMP';
                $insert .= $and;
                $insert .= is_null($genre['deleted_at']) ? 'NULL' : 'CURRENT_TIMESTAMP';
                $insert .= ');';

                echo $insert . "\n";

                $this->genres[] = $genre['name'];
            }
        }
    }

    public function setLots($lots): void
    {
        $and = ',';

        echo 'TRUNCATE TABLE CLUBS_LOTS;' . "\n";
        echo 'TRUNCATE TABLE CLUBS_COPIES;' . "\n";
        echo 'TRUNCATE TABLE CLUBS_HISTORIC;' . "\n";

        $id = 1;

        foreach ($lots as $lot) {
            if (!empty($lot['title'])) {
                $insert = 'INSERT INTO CLUBS_LOTS (ID,ACTIVE,AUTHORSHIP,TITLE,OBSERVATIONS,LANG_ES,LANG_CAT,LANG_OTHERS,LANG_SUM,YEAR,WAREHOUSE,STATUS_ID,GENRE_ID,UPDATED_AT,CREATED_AT,DELETED_AT) VALUES (';
                $insert .= ($id++) . $and;
                $insert .= 0; //is_null($lot['deleted_at']) ? $active : 0;
                $insert .= $and;
                $insert .= "'" . $this->scapeCharacters($lot['authorship']) . "'" . $and;
                $insert .= "'" . $this->scapeCharacters($lot['title']) . "'" . $and;
                $insert .= "'" . $this->scapeCharacters($lot['description']) . "'" . $and;
                $insert .= $lot['lang_es'] ?? 0;
                $insert .= $and;
                $insert .= $lot['lang_cat'] ?? 0;
                $insert .= $and;
                $insert .= $lot['lang_others'] ?? 0;
                $insert .= $and;
                $insert .= $lot['lang_es'] + $lot['lang_cat'] + $lot['lang_others'] + 0;
                $insert .= $and;
                $insert .= $lot['year'] . $and;
                $insert .= $lot['deleted_at'] === 1 ? 'NULL' : "'" . $this->scapeCharacters($lot['warehouse']) . "'";
                $insert .= $and;
                $insert .= 1 . $and;
                $insert .= (array_search($lot['genre'], $this->genres) + 1) . $and;
                $insert .= !empty($lot['updated_at']) ? "TO_TIMESTAMP('" . $lot['updated_at'] . "','yyyy-mm-dd HH24:MI:SS.FF')" : 'CURRENT_TIMESTAMP';
                $insert .= $and;
                $insert .= !empty($lot['created_at']) ? "TO_TIMESTAMP('" . $lot['created_at'] . "','yyyy-mm-dd HH24:MI:SS.FF')" : 'CURRENT_TIMESTAMP';
                $insert .= $and;
                $insert .= $lot['deleted_at'] === 1 ? 'CURRENT_TIMESTAMP' : 'NULL';
                $insert .= ');';

                //if ( $lot['lang_others'] > 0 )  echo '"' . ($id-1) . '","' . $lot['title'] . '","' . $lot['authorship'] . '","' . $lot['lang_others'] . "\"\n";

                echo $insert . "\n";
            }
        }

        echo 'UPDATE CLUBS_LOTS SET DELETED_AT = CURRENT_TIMESTAMP WHERE ID = 1073;' . "\n";
    }

    public function setClubs($clubs): void
    {
        $and = ',';

        echo 'TRUNCATE TABLE CLUBS_CLUBS;' . "\n";

        foreach ($clubs as $key => $club) {
            if (!empty($club['name'])) {
                $stid = oci_parse($this->db, "SELECT * 
                                              FROM CLUBS_LIBRARIES
                                              WHERE CODE = '" . trim($club['code']) . "'
                                              OR EMAIL = '" . preg_replace('/\xc2\xa0/', '', trim($club['email'])) . "'");

                oci_execute($stid);

                $library = 0;

                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $library = $row['ID'];
                }

                if ($library === 0) {
                    echo "Club ". $club['name'] ." :: ". $club['active'] ." :: ". $club['email'] ." :: ". $club['code'] ."\n";
                } else {
                    $insert = 'INSERT INTO CLUBS_CLUBS (ID,LIBRARY_ID,NAME,YEAR,ACTIVE,DESCRIPTION,CREATED_AT,UPDATED_AT,DELETED_AT) VALUES (';
                    $insert .= ($key + 1) . $and;
                    $insert .= $library . $and;
                    $insert .= "'" . $this->scapeCharacters($club['name']) . "'" . $and;
                    $insert .= $club['year'] . $and;
                    $insert .= $club['active'] ?? 0;
                    $insert .= $and;
                    $insert .= "'" . $this->scapeCharacters($club['description']) . "'" . $and;
                    $insert .= !empty($lot['created_at']) ? "TO_TIMESTAMP('" . $lot['created_at'] . "','yyyy-mm-dd HH24:MI:SS.FF')" : 'CURRENT_TIMESTAMP';
                    $insert .= $and;
                    $insert .= !empty($lot['updated_at']) ? "TO_TIMESTAMP('" . $lot['updated_at'] . "','yyyy-mm-dd HH24:MI:SS.FF')" : 'CURRENT_TIMESTAMP';
                    $insert .= $and;
                    $insert .= $club['deleted_at'] === 1 ? 'CURRENT_TIMESTAMP' : 'NULL';
                    $insert .= ');';

                    echo $insert . "\n";

                    if ($club['deleted_at'] !== 1) {
                        echo 'UPDATE CLUBS_LIBRARIES SET TOTAL_CLUBS = TOTAL_CLUBS+1 WHERE ID = ' . $library . ';' . "\n";
                    }
                }
            }
        }
    }

    public function setHistoric($historic): void
    {
        $and = ',';
        $id = 0;

        echo 'TRUNCATE TABLE CLUBS_HISTORIC;' . "\n";

        foreach ($historic as $key => $item) {
            if (!empty($item['name'])) {
                $stid = oci_parse($this->db, "SELECT * 
                                              FROM CLUBS_LIBRARIES
                                              WHERE CODE = '" . trim($item['code']) . "'
                                              OR EMAIL = '" . preg_replace('/\xc2\xa0/', '', trim($item['email'])) . "'");

                oci_execute($stid);

                $library = 0;
                $count = 0;

                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $count++;
                    $library = $row['ID'];
                }

                if ($count !== 1 || $library === 0) {
                    echo "-------------------------------------------------------\n";
                    echo "library $count - ". $item['name'] ." :: ". $item['active'] ." :: ". $item['email'] ." :: ". $item['code'] ."\n";
                    echo "-------------------------------------------------------\n";
                } else {
                    $query = "SELECT * 
                              FROM CLUBS_CLUBS
                              WHERE LIBRARY_ID = {$library}
                              AND YEAR = " . $item['year'] . "
                              AND NAME LIKE '" . $this->likeName($item['name']) . "'";

                    $stid = oci_parse($this->db, $query);

                    oci_execute($stid);

                    $club = 0;
                    $count = 0;

                    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                        $count++;
                        $club = $row['ID'];
                    }

                    if ($count !== 1 || $club === 0) {
                        echo "-------------------------------------------------------\n";
                        echo "club $count - ". $item['name'] ." \n ". $query ."\n";
                        echo "-------------------------------------------------------\n";
                    } else {
                        $query = 'SELECT * 
                                  FROM CLUBS_LOTS
                                  WHERE DELETED_AT IS NULL ';

                        if (!empty($item['authorship'])) {
                            $query .= "AND AUTHORSHIP LIKE '" . $this->likeName(trim($item['authorship'])) . "' ";
                        }

                        $query .= "AND TITLE LIKE '" . $this->likeName(trim($item['title'])) . "'
                                   AND YEAR = " . $item['lyear'] . ' ';

                        if (!empty($item['warehouse'])) {
                            $query .= "AND WAREHOUSE LIKE '" . $this->likeName(trim($item['warehouse'])) . "' ";
                        }

                        $stid = oci_parse($this->db, $query);

                        oci_execute($stid);

                        $count = 0;
                        $lot = 0;
                        $lots = [];

                        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                            $count++;
                            $lot = $row['ID'];
                            $lots[] = $row;
                        }

                        if ($count !== 1 || $lot === 0) {
                            echo "-------------------------------------------------------\n";
                            echo "lot $count - ". $item['name'] ." \n ". $query ."\n";
                            print_r($lots);
                            echo "-------------------------------------------------------\n";
                        } else {
                            if ($item['periodo_id'] === 35) {
                                $insert = 'INSERT INTO CLUBS_HISTORIC (ID,LOT_ID,LIBRARY_ID,CLUB_ID,RETURN_IN,TRANSIT_IN,RECEIVED_AT,UPDATED_AT,CREATED_AT,SEND_ID) VALUES (';
                                $insert .= $id++ . $and;
                                $insert .= $lot . $and;
                                $insert .= $library . $and;
                                $insert .= $club . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['endend'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['initial'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['initial'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['initial'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['initial'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "'" . $this->scapeCharacters($item['periodo']) . "'";
                                $insert .= ');';

                                echo $insert . "\n";

                                echo 'UPDATE CLUBS_CLUBS SET TOTAL_LOTS = TOTAL_LOTS+1 WHERE ID = ' . $club . ';' . "\n";
                                echo 'UPDATE CLUBS_LIBRARIES SET TOTAL_LOTS = TOTAL_LOTS+1 WHERE ID = ' . $library . ';' . "\n";

                                echo 'UPDATE CLUBS_CLUBS SET USE_LOTS = USE_LOTS+1 WHERE ID = ' . $club . ';' . "\n";
                                echo 'UPDATE CLUBS_LIBRARIES SET USE_LOTS = USE_LOTS+1 WHERE ID = ' . $library . ';' . "\n";

                                echo 'UPDATE CLUBS_LOTS SET STATUS_ID = 5 WHERE ID = ' . $lot . ';' . "\n";
                            } else {
                                $insert = 'INSERT INTO CLUBS_HISTORIC (ID,LOT_ID,LIBRARY_ID,CLUB_ID,RETURN_IN,TRANSIT_IN,RECEIVED_AT,RETURNED_AT,PICKED_AT,CLOSED_AT,RECEIVED_IN,UPDATED_AT,CREATED_AT,SEND_ID) VALUES (';
                                $insert .= $id++ . $and;
                                $insert .= $lot . $and;
                                $insert .= $library . $and;
                                $insert .= $club . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['endend'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['initial'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['initial'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['endend'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['endend'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['endend'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['endend'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['endend'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "TO_TIMESTAMP('" . $item['initial'] . "','yyyy-mm-dd HH24:MI:SS.FF')" . $and;
                                $insert .= "'" . $this->scapeCharacters($item['periodo']) . "'";
                                $insert .= ');';

                                echo $insert . "\n";

                                echo 'UPDATE CLUBS_CLUBS SET TOTAL_LOTS = TOTAL_LOTS+1 WHERE ID = ' . $club . ';' . "\n";
                                echo 'UPDATE CLUBS_LIBRARIES SET TOTAL_LOTS = TOTAL_LOTS+1 WHERE ID = ' . $library . ';' . "\n";
                            }
                        }
                    }

                    echo $insert . "\n";
                }
            }
        }
    }

    public function setCodeLots(): void
    {
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000009995427' WHERE Warehouse = 'C-3-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010538802' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798428' WHERE Warehouse = 'A-2-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798429' WHERE Warehouse = 'B-2-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798430' WHERE Warehouse = 'B-4-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798431' WHERE Warehouse = 'C-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798432' WHERE Warehouse = 'C-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798433' WHERE Warehouse = 'CBB-07';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798434' WHERE Warehouse = 'CBB-28';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798436' WHERE Warehouse = 'D-5-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798437' WHERE Warehouse = 'E-10-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798438' WHERE Warehouse = 'E-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798439' WHERE Warehouse = 'E-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798440' WHERE Warehouse = 'E-4-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798441' WHERE Warehouse = 'E-4-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798442' WHERE Warehouse = 'E-4-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798443' WHERE Warehouse = 'E-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798444' WHERE Warehouse = 'E-8-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798445' WHERE Warehouse = 'E-9-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798446' WHERE Warehouse = 'E-D-25';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798447' WHERE Warehouse = 'E-D-25';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798448' WHERE Warehouse = 'E-D-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798449' WHERE Warehouse = 'E-D-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798450' WHERE Warehouse = 'F-3-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798451' WHERE Warehouse = 'F-4-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798452' WHERE Warehouse = 'G-1-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798453' WHERE Warehouse = 'G-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798454' WHERE Warehouse = 'G-2-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798455' WHERE Warehouse = 'G-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798456' WHERE Warehouse = 'G-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798457' WHERE Warehouse = 'G-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798459' WHERE Warehouse = 'H-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798460' WHERE Warehouse = 'H-1-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798461' WHERE Warehouse = 'H-2-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798462' WHERE Warehouse = 'H-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798463' WHERE Warehouse = 'H-4-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798464' WHERE Warehouse = 'I-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798465' WHERE Warehouse = 'P-D-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798466' WHERE Warehouse = 'P-A-4-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798467' WHERE Warehouse = 'P-B-1-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798468' WHERE Warehouse = 'P-B-2-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798469' WHERE Warehouse = 'P-B-4-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798470' WHERE Warehouse = 'P-D-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798471' WHERE Warehouse = 'P-E-9-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798472' WHERE Warehouse = 'P-F-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798473' WHERE Warehouse = 'P-F-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798474' WHERE Warehouse = 'P-F-5-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798475' WHERE Warehouse = 'P-G-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798476' WHERE Warehouse = 'P-G-2-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798477' WHERE Warehouse = 'P-I-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798478' WHERE Warehouse = 'P-A-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798479' WHERE Warehouse = 'CBB-85';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798480' WHERE Warehouse = 'CBB-31';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798481' WHERE Warehouse = 'CBB-70';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798482' WHERE Warehouse = 'P-E-7-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798483' WHERE Warehouse = 'CBB-51';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798484' WHERE Warehouse = 'D-4-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798485' WHERE Warehouse = 'E-4-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798486' WHERE Warehouse = 'P-A-1-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798487' WHERE Warehouse = 'P-F-3-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798488' WHERE Warehouse = 'H-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798489' WHERE Warehouse = 'P-I-4-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798490' WHERE Warehouse = 'P-F-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798491' WHERE Warehouse = 'E-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798492' WHERE Warehouse = 'P-E-5-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798493' WHERE Warehouse = 'P-F-4-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798495' WHERE Warehouse = 'B-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798496' WHERE Warehouse = 'E-8-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798497' WHERE Warehouse = 'E-3-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798498' WHERE Warehouse = 'P-F-1-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798499' WHERE Warehouse = 'P-D-5-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798500' WHERE Warehouse = 'F-2-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798501' WHERE Warehouse = 'H-1-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798502' WHERE Warehouse = 'CBB-69';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798503' WHERE Warehouse = 'CBB-69';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798504' WHERE Warehouse = 'I-2-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798505' WHERE Warehouse = 'P-D-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798506' WHERE Warehouse = 'P-F-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798507' WHERE Warehouse = 'H-2-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798508' WHERE Warehouse = 'A-4-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798509' WHERE Warehouse = 'I-1-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798510' WHERE Warehouse = 'F-3-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798511' WHERE Warehouse = 'F-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798512' WHERE Warehouse = 'P-E-6-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798513' WHERE Warehouse = 'D-3-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798514' WHERE Warehouse = 'G-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798515' WHERE Warehouse = 'C-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798516' WHERE Warehouse = 'E-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798517' WHERE Warehouse = 'B-3-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798518' WHERE Warehouse = 'F-2-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798519' WHERE Warehouse = 'CBB-03';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798520' WHERE Warehouse = 'CBB-03';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798521' WHERE Warehouse = 'D-3-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798522' WHERE Warehouse = 'E-D-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798523' WHERE Warehouse = 'E-D-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798524' WHERE Warehouse = 'C-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798525' WHERE Warehouse = 'A-1-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798526' WHERE Warehouse = 'H-3-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798527' WHERE Warehouse = 'P-A-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798528' WHERE Warehouse = 'CBB-40';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798529' WHERE Warehouse = 'CBB-40';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798530' WHERE Warehouse = 'H-4-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798531' WHERE Warehouse = 'P-E-6-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798532' WHERE Warehouse = 'F-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798533' WHERE Warehouse = 'C-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798534' WHERE Warehouse = 'P-G-4-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798535' WHERE Warehouse = 'CBB-44';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798536' WHERE Warehouse = 'CBB-44';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798537' WHERE Warehouse = 'G-1-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798538' WHERE Warehouse = 'E-3-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798539' WHERE Warehouse = 'E-3-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798540' WHERE Warehouse = 'CBB-74';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798541' WHERE Warehouse = 'E-1-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798542' WHERE Warehouse = 'E-2-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798543' WHERE Warehouse = 'P-D-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798544' WHERE Warehouse = 'A-4-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798545' WHERE Warehouse = 'CBB-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798546' WHERE Warehouse = 'B-5-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798547' WHERE Warehouse = 'B-5-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798549' WHERE Warehouse = 'A-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798551' WHERE Warehouse = 'E-7-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798554' WHERE Warehouse = 'E-3-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798555' WHERE Warehouse = 'E-9-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798556' WHERE Warehouse = 'H-4-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798557' WHERE Warehouse = 'A-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798558' WHERE Warehouse = 'C-1-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798559' WHERE Warehouse = 'E-7-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798560' WHERE Warehouse = 'E-D-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798561' WHERE Warehouse = 'E-D-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798562' WHERE Warehouse = 'E-D-26';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798563' WHERE Warehouse = 'E-D-26';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798564' WHERE Warehouse = 'F-2-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798565' WHERE Warehouse = 'E-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798566' WHERE Warehouse = 'H-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798567' WHERE Warehouse = 'CBB-09';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798568' WHERE Warehouse = 'E-D-40';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798569' WHERE Warehouse = 'E-D-40';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798570' WHERE Warehouse = 'E-10-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798571' WHERE Warehouse = 'I-3-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798572' WHERE Warehouse = 'P-A-1-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798573' WHERE Warehouse = 'H-4-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798574' WHERE Warehouse = 'CBB-34';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798575' WHERE Warehouse = 'E-D-34';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798576' WHERE Warehouse = 'E-D-34';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798577' WHERE Warehouse = 'E-D-23';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798578' WHERE Warehouse = 'E-D-23';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798579' WHERE Warehouse = 'I-3-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798580' WHERE Warehouse = 'A-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798581' WHERE Warehouse = 'E-10-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798582' WHERE Warehouse = 'E-D-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798583' WHERE Warehouse = 'E-D-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798584' WHERE Warehouse = 'F-4-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798585' WHERE Warehouse = 'H-2-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798586' WHERE Warehouse = 'E-1-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798587' WHERE Warehouse = 'H-4-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798588' WHERE Warehouse = 'P-B-2-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798589' WHERE Warehouse = 'A-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798590' WHERE Warehouse = 'C-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798591' WHERE Warehouse = 'E-9-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798592' WHERE Warehouse = 'B-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798593' WHERE Warehouse = 'E-4-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798594' WHERE Warehouse = 'A-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798595' WHERE Warehouse = 'CBB-57';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798596' WHERE Warehouse = 'P-C-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798598' WHERE Warehouse = 'F-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798599' WHERE Warehouse = 'B-4-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798600' WHERE Warehouse = 'C-1-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798601' WHERE Warehouse = 'D-5-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798602' WHERE Warehouse = 'E-8-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798603' WHERE Warehouse = 'P-B-5-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798604' WHERE Warehouse = 'A-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798606' WHERE Warehouse = 'E-D-36';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798607' WHERE Warehouse = 'E-D-36';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798608' WHERE Warehouse = 'I-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798609' WHERE Warehouse = 'B-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798610' WHERE Warehouse = 'E-D-37';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798611' WHERE Warehouse = 'E-D-37';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798612' WHERE Warehouse = 'H-2-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798613' WHERE Warehouse = 'H-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798614' WHERE Warehouse = 'H-3-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798616' WHERE Warehouse = 'I-3-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798617' WHERE Warehouse = 'P-C-5-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798618' WHERE Warehouse = 'A-1-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798619' WHERE Warehouse = 'I-3-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798620' WHERE Warehouse = 'P-G-2-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798621' WHERE Warehouse = 'B-2-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798622' WHERE Warehouse = 'E-5-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798623' WHERE Warehouse = 'A-1-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798624' WHERE Warehouse = 'CBB-61';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798625' WHERE Warehouse = 'E-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798626' WHERE Warehouse = 'H-2-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798627' WHERE Warehouse = 'I-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798628' WHERE Warehouse = 'E-7-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798629' WHERE Warehouse = 'E-9-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798630' WHERE Warehouse = 'P-B-5-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798631' WHERE Warehouse = 'B-3-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798632' WHERE Warehouse = 'E-D-31';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798633' WHERE Warehouse = 'E-D-31';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798634' WHERE Warehouse = 'H-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798635' WHERE Warehouse = 'C-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798636' WHERE Warehouse = 'CBB-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798637' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798638' WHERE Warehouse = 'P-E-9-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798639' WHERE Warehouse = 'P-D-5-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798640' WHERE Warehouse = 'G-3-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798641' WHERE Warehouse = 'B-4-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798642' WHERE Warehouse = 'E-1-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798643' WHERE Warehouse = 'E-7-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798644' WHERE Warehouse = 'A-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798645' WHERE Warehouse = 'E-10-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798646' WHERE Warehouse = 'P-I-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798647' WHERE Warehouse = 'E-D-33';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798648' WHERE Warehouse = 'E-D-33';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798649' WHERE Warehouse = 'H-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798650' WHERE Warehouse = 'H-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798651' WHERE Warehouse = 'I-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798652' WHERE Warehouse = 'A-2-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798653' WHERE Warehouse = 'E-8-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798654' WHERE Warehouse = 'G-1-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798655' WHERE Warehouse = 'H-4-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798656' WHERE Warehouse = 'P-E-7-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798657' WHERE Warehouse = 'B-4-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798658' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798659' WHERE Warehouse = 'H-4-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798660' WHERE Warehouse = 'A-1-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798661' WHERE Warehouse = 'A-3-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798662' WHERE Warehouse = 'A-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798663' WHERE Warehouse = 'B-3-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798664' WHERE Warehouse = 'E-7-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798665' WHERE Warehouse = 'G-1-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798666' WHERE Warehouse = 'I-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798667' WHERE Warehouse = 'CBB-32';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798668' WHERE Warehouse = 'CBB-32';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798669' WHERE Warehouse = 'G-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798670' WHERE Warehouse = 'E-D-21';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798671' WHERE Warehouse = 'E-D-21';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798672' WHERE Warehouse = 'I-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798673' WHERE Warehouse = 'I-1-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798674' WHERE Warehouse = 'P-G-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798675' WHERE Warehouse = 'P-C-5-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798676' WHERE Warehouse = 'P-G-5-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798677' WHERE Warehouse = 'C-4-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798678' WHERE Warehouse = 'E-5-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798679' WHERE Warehouse = 'D-5-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798680' WHERE Warehouse = 'CBB-67';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798681' WHERE Warehouse = 'C-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798683' WHERE Warehouse = 'E-10-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798684' WHERE Warehouse = 'P-A-5-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798685' WHERE Warehouse = 'P-A-2-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798686' WHERE Warehouse = 'C-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798687' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798688' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798689' WHERE Warehouse = 'D-5-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798690' WHERE Warehouse = 'CBB-84';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798692' WHERE Warehouse = 'P-G-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798693' WHERE Warehouse = 'E-3-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798694' WHERE Warehouse = 'CBB-54';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798695' WHERE Warehouse = 'F-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798696' WHERE Warehouse = 'E-4-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798697' WHERE Warehouse = 'D-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798698' WHERE Warehouse = 'P-B-5-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798699' WHERE Warehouse = 'A-2-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798700' WHERE Warehouse = 'F-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798701' WHERE Warehouse = 'B-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798702' WHERE Warehouse = 'I-2-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798703' WHERE Warehouse = 'I-1-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798705' WHERE Warehouse = 'I-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798706' WHERE Warehouse = 'C-2-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798707' WHERE Warehouse = 'E-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798708' WHERE Warehouse = 'I-1-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798709' WHERE Warehouse = 'I-1-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798710' WHERE Warehouse = 'D-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798711' WHERE Warehouse = 'D-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798712' WHERE Warehouse = 'P-I-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798713' WHERE Warehouse = 'P-G-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798714' WHERE Warehouse = 'P-E-5-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798715' WHERE Warehouse = 'E-7-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798716' WHERE Warehouse = 'E-9-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798717' WHERE Warehouse = 'E-D-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798718' WHERE Warehouse = 'E-D-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798719' WHERE Warehouse = 'F-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798720' WHERE Warehouse = 'P-B-1-5a';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798721' WHERE Warehouse = 'A-2-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798722' WHERE Warehouse = 'P-D-5-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798723' WHERE Warehouse = 'D-4-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798724' WHERE Warehouse = 'F-4-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798725' WHERE Warehouse = 'P-G-5-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798726' WHERE Warehouse = 'C-2-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798727' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798728' WHERE Warehouse = 'P-A-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798729' WHERE Warehouse = 'P-B-4-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798730' WHERE Warehouse = 'P-D-5-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798731' WHERE Warehouse = 'P-E-7-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798732' WHERE Warehouse = 'A-4-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798733' WHERE Warehouse = 'C-1-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798734' WHERE Warehouse = 'CBB-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798735' WHERE Warehouse = 'D-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798736' WHERE Warehouse = 'D-4-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798738' WHERE Warehouse = 'G-3-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798739' WHERE Warehouse = 'E-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798740' WHERE Warehouse = 'P-A-4-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798741' WHERE Warehouse = 'P-F-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798742' WHERE Warehouse = 'C-2-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798743' WHERE Warehouse = 'G-1-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798745' WHERE Warehouse = 'P-E-1-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798746' WHERE Warehouse = 'B-4-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798747' WHERE Warehouse = 'I-3-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798748' WHERE Warehouse = 'P-F-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798749' WHERE Warehouse = 'E-1-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798750' WHERE Warehouse = 'H-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798751' WHERE Warehouse = 'P-I-5-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798752' WHERE Warehouse = 'B-4-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798753' WHERE Warehouse = 'E-7-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798754' WHERE Warehouse = 'C-1-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798755' WHERE Warehouse = 'H-1-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798756' WHERE Warehouse = 'P-B-2-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798757' WHERE Warehouse = 'P-F-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798758' WHERE Warehouse = 'A-4-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798759' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798760' WHERE Warehouse = 'G-1-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798761' WHERE Warehouse = 'I-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798762' WHERE Warehouse = 'P-E-9-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798763' WHERE Warehouse = 'D-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798764' WHERE Warehouse = 'H-4-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798765' WHERE Warehouse = 'B-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798766' WHERE Warehouse = 'G-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798767' WHERE Warehouse = 'P-C-1-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798768' WHERE Warehouse = 'F-2-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798769' WHERE Warehouse = 'F-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798770' WHERE Warehouse = 'H-2-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798771' WHERE Warehouse = 'CBB-63';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798772' WHERE Warehouse = 'P-B-4-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798773' WHERE Warehouse = 'P-C-4-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798774' WHERE Warehouse = 'P-E-10-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798776' WHERE Warehouse = 'CBB-75';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798777' WHERE Warehouse = 'D-2-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798778' WHERE Warehouse = 'H-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798779' WHERE Warehouse = 'P-A-5-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798780' WHERE Warehouse = 'CBB-38';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798781' WHERE Warehouse = 'P-A-4-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798782' WHERE Warehouse = 'A-4-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798783' WHERE Warehouse = 'D-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798784' WHERE Warehouse = 'G-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798785' WHERE Warehouse = 'P-C-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798786' WHERE Warehouse = 'F-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798787' WHERE Warehouse = 'G-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798788' WHERE Warehouse = 'P-D-1-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798789' WHERE Warehouse = 'B-3-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798790' WHERE Warehouse = 'D-4-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798791' WHERE Warehouse = 'E-8-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798792' WHERE Warehouse = 'B-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798795' WHERE Warehouse = 'I-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798796' WHERE Warehouse = 'D-2-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798797' WHERE Warehouse = 'D-2-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798798' WHERE Warehouse = 'F-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798799' WHERE Warehouse = 'E-1-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798800' WHERE Warehouse = 'G-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798801' WHERE Warehouse = 'I-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798802' WHERE Warehouse = 'P-A-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798803' WHERE Warehouse = 'P-C-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798804' WHERE Warehouse = 'P-E-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798805' WHERE Warehouse = 'P-D-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798806' WHERE Warehouse = 'P-F-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798807' WHERE Warehouse = 'C-2-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798808' WHERE Warehouse = 'E-7-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798809' WHERE Warehouse = 'P-E-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798810' WHERE Warehouse = 'CBB-60';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798810' WHERE Warehouse = 'H-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798811' WHERE Warehouse = 'CBB-02';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798812' WHERE Warehouse = 'CBB-81';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798813' WHERE Warehouse = 'CBB-08';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798814' WHERE Warehouse = 'P-E-4-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798815' WHERE Warehouse = 'E-10-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798817' WHERE Warehouse = 'P-E-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798818' WHERE Warehouse = 'E-2-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798819' WHERE Warehouse = 'CBB-55';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798820' WHERE Warehouse = 'CBB-55';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798821' WHERE Warehouse = 'P-G-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798822' WHERE Warehouse = 'A-3-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798823' WHERE Warehouse = 'E-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798824' WHERE Warehouse = 'D-4-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798825' WHERE Warehouse = 'F-4-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798826' WHERE Warehouse = 'P-A-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798827' WHERE Warehouse = 'CBB-91';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798828' WHERE Warehouse = 'D-3-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798829' WHERE Warehouse = 'P-D-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798830' WHERE Warehouse = 'C-4-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798831' WHERE Warehouse = 'D-5-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798832' WHERE Warehouse = 'F-4-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798833' WHERE Warehouse = 'P-I-5-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798834' WHERE Warehouse = 'A-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798835' WHERE Warehouse = 'E-3-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798836' WHERE Warehouse = 'A-1-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798837' WHERE Warehouse = 'B-4-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798838' WHERE Warehouse = 'B-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798839' WHERE Warehouse = 'I-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798840' WHERE Warehouse = 'A-2-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798841' WHERE Warehouse = 'A-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798842' WHERE Warehouse = 'F-2-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798843' WHERE Warehouse = 'P-C-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798844' WHERE Warehouse = 'D-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798845' WHERE Warehouse = 'CBB-66';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798846' WHERE Warehouse = 'I-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798847' WHERE Warehouse = 'P-C-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798848' WHERE Warehouse = 'A-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798849' WHERE Warehouse = 'E-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798850' WHERE Warehouse = 'P-D-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798851' WHERE Warehouse = 'A-1-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798852' WHERE Warehouse = 'D-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798853' WHERE Warehouse = 'A-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798854' WHERE Warehouse = 'A-1-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798855' WHERE Warehouse = 'E-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798856' WHERE Warehouse = 'D-3-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798857' WHERE Warehouse = 'I-2-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798858' WHERE Warehouse = 'E-9-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798859' WHERE Warehouse = 'P-F-2-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798860' WHERE Warehouse = 'H-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798861' WHERE Warehouse = 'G-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798862' WHERE Warehouse = 'P-B-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798863' WHERE Warehouse = 'H-1-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798864' WHERE Warehouse = 'P-F-4-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798865' WHERE Warehouse = 'P-A-5-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798866' WHERE Warehouse = 'P-D-2-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798867' WHERE Warehouse = 'E-D-24';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798868' WHERE Warehouse = 'E-D-24';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798869' WHERE Warehouse = 'F-4-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798870' WHERE Warehouse = 'H-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798871' WHERE Warehouse = 'P-G-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798872' WHERE Warehouse = 'P-D-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798873' WHERE Warehouse = 'B-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798874' WHERE Warehouse = 'D-4-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798875' WHERE Warehouse = 'E-7-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798876' WHERE Warehouse = 'P-D-1-2a';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798877' WHERE Warehouse = 'P-E-9-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798878' WHERE Warehouse = 'P-D-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798879' WHERE Warehouse = 'H-4-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798880' WHERE Warehouse = 'H-1-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798881' WHERE Warehouse = 'E-3-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798882' WHERE Warehouse = 'P-C-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798883' WHERE Warehouse = 'C-1-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798884' WHERE Warehouse = 'P-B-5-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798885' WHERE Warehouse = 'C-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798886' WHERE Warehouse = 'P-I-4-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798887' WHERE Warehouse = 'E-8-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798888' WHERE Warehouse = 'H-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798889' WHERE Warehouse = 'P-C-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798890' WHERE Warehouse = 'D-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798891' WHERE Warehouse = 'P-I-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798892' WHERE Warehouse = 'P-I-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798893' WHERE Warehouse = 'D-3-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798894' WHERE Warehouse = 'P-I-1-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798895' WHERE Warehouse = 'E-7-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798896' WHERE Warehouse = 'E-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798897' WHERE Warehouse = 'P-E-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798898' WHERE Warehouse = 'C-3-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798899' WHERE Warehouse = 'P-I-4-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798900' WHERE Warehouse = 'E-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798901' WHERE Warehouse = 'P-G-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798902' WHERE Warehouse = 'D-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798903' WHERE Warehouse = 'A-2-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798904' WHERE Warehouse = 'E-10-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798905' WHERE Warehouse = 'P-A-4-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798906' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798907' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798908' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798909' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798910' WHERE Warehouse = 'CBB-97';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798911' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798912' WHERE Warehouse = 'CBB-99';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798913' WHERE Warehouse = 'CBB-100';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798914' WHERE Warehouse = 'P-B-3-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798915' WHERE Warehouse = 'E-4-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798916' WHERE Warehouse = 'E-9-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798917' WHERE Warehouse = 'D-3-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798918' WHERE Warehouse = 'D-4-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798919' WHERE Warehouse = 'P-F-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798920' WHERE Warehouse = 'I-1-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798921' WHERE Warehouse = 'E-8-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798922' WHERE Warehouse = 'P-A-5-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798923' WHERE Warehouse = 'P-I-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798924' WHERE Warehouse = 'H-3-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798925' WHERE Warehouse = 'H-3-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798926' WHERE Warehouse = 'C-1-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798927' WHERE Warehouse = 'G-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798928' WHERE Warehouse = 'P-C-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798929' WHERE Warehouse = 'I-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798930' WHERE Warehouse = 'P-C-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798931' WHERE Warehouse = 'P-E-4-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798932' WHERE Warehouse = 'F-2-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798933' WHERE Warehouse = 'P-D-5-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798934' WHERE Warehouse = 'B-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798935' WHERE Warehouse = 'B-2-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798936' WHERE Warehouse = 'A-4-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798937' WHERE Warehouse = 'F-4-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798938' WHERE Warehouse = 'G-1-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798939' WHERE Warehouse = 'P-G-5-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798940' WHERE Warehouse = 'B-5-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798941' WHERE Warehouse = 'P-I-5-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798942' WHERE Warehouse = 'CBB-82';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798943' WHERE Warehouse = 'P-B-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798944' WHERE Warehouse = 'D-5-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798945' WHERE Warehouse = 'E-8-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798946' WHERE Warehouse = 'P-F-4-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798947' WHERE Warehouse = 'P-E-8-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798948' WHERE Warehouse = 'P-A-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798949' WHERE Warehouse = 'G-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798950' WHERE Warehouse = 'F-4-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798951' WHERE Warehouse = 'E-D-27';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798952' WHERE Warehouse = 'E-D-27';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798953' WHERE Warehouse = 'E-2-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798954' WHERE Warehouse = 'G-2-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798955' WHERE Warehouse = 'E-1-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798956' WHERE Warehouse = 'P-D-4-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798977' WHERE Warehouse = 'C-3-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798978' WHERE Warehouse = 'B-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798979' WHERE Warehouse = 'B-3-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798980' WHERE Warehouse = 'E-D-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798981' WHERE Warehouse = 'E-D-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798982' WHERE Warehouse = 'P-B-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798983' WHERE Warehouse = 'CBB-0';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798984' WHERE Warehouse = 'E-D-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010798985' WHERE Warehouse = 'E-D-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799002' WHERE Warehouse = 'E-5-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799003' WHERE Warehouse = 'E-D-32';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799004' WHERE Warehouse = 'E-D-32';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799055' WHERE Warehouse = 'E-4-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799056' WHERE Warehouse = 'C-1-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799057' WHERE Warehouse = 'C-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799058' WHERE Warehouse = 'A-4-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799059' WHERE Warehouse = 'P-I-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799060' WHERE Warehouse = 'E-D-29';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799061' WHERE Warehouse = 'E-D-29';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799062' WHERE Warehouse = 'H-2-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799063' WHERE Warehouse = 'E-D-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799064' WHERE Warehouse = 'E-D-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799065' WHERE Warehouse = 'F-2-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799066' WHERE Warehouse = 'A-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799067' WHERE Warehouse = 'P-I-1-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799068' WHERE Warehouse = 'I-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799070' WHERE Warehouse = 'C-2-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799071' WHERE Warehouse = 'C-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799072' WHERE Warehouse = 'P-A-5-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799073' WHERE Warehouse = 'P-D-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799074' WHERE Warehouse = 'B-2-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799075' WHERE Warehouse = 'C-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799076' WHERE Warehouse = 'E-D-39';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799077' WHERE Warehouse = 'E-D-39';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799078' WHERE Warehouse = 'D-5-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799079' WHERE Warehouse = 'P-F-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799080' WHERE Warehouse = 'C-3-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799081' WHERE Warehouse = 'D-2-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799117' WHERE Warehouse = 'B-4-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799118' WHERE Warehouse = 'CBB-62';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799119' WHERE Warehouse = 'P-A-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799120' WHERE Warehouse = 'P-D-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799121' WHERE Warehouse = 'P-E-6-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799123' WHERE Warehouse = 'E-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799124' WHERE Warehouse = 'F-4-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799125' WHERE Warehouse = 'G-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799126' WHERE Warehouse = 'P-B-5-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799127' WHERE Warehouse = 'I-1-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799128' WHERE Warehouse = 'A-1-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799129' WHERE Warehouse = 'D-2-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799130' WHERE Warehouse = 'F-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799151' WHERE Warehouse = 'D-5-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799152' WHERE Warehouse = 'E-9-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799153' WHERE Warehouse = 'P-C-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799154' WHERE Warehouse = 'E-10-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799155' WHERE Warehouse = 'E-8-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799156' WHERE Warehouse = 'B-5-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799157' WHERE Warehouse = 'I-1-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799158' WHERE Warehouse = 'H-4-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799159' WHERE Warehouse = 'P-I-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799160' WHERE Warehouse = 'P-A-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799161' WHERE Warehouse = 'P-D-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799162' WHERE Warehouse = 'E-7-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799163' WHERE Warehouse = 'F-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799164' WHERE Warehouse = 'G-1-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799165' WHERE Warehouse = 'E-5-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799166' WHERE Warehouse = 'A-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799167' WHERE Warehouse = 'E-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799168' WHERE Warehouse = 'E-D-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799169' WHERE Warehouse = 'E-D-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799170' WHERE Warehouse = 'P-B-5-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799171' WHERE Warehouse = 'E-7-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799172' WHERE Warehouse = 'B-2-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799173' WHERE Warehouse = 'D-4-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799174' WHERE Warehouse = 'E-9-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799175' WHERE Warehouse = 'H-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799176' WHERE Warehouse = 'E-4-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799177' WHERE Warehouse = 'P-B-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799178' WHERE Warehouse = 'P-I-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799244' WHERE Warehouse = 'H-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799245' WHERE Warehouse = 'H-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799326' WHERE Warehouse = 'A-3-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799327' WHERE Warehouse = 'B-5-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799328' WHERE Warehouse = 'P-A-1-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799329' WHERE Warehouse = 'D-3-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799350' WHERE Warehouse = 'CBB-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799351' WHERE Warehouse = 'CBB-23';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799352' WHERE Warehouse = 'CBB-43';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799353' WHERE Warehouse = 'CBB-79';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799375' WHERE Warehouse = 'D-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799376' WHERE Warehouse = 'G-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799377' WHERE Warehouse = 'G-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799378' WHERE Warehouse = 'F-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799379' WHERE Warehouse = 'C-1-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799380' WHERE Warehouse = 'CBB-104';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799381' WHERE Warehouse = 'A-4-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799382' WHERE Warehouse = 'E-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799383' WHERE Warehouse = 'F-2-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799384' WHERE Warehouse = 'F-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799385' WHERE Warehouse = 'P-I-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799387' WHERE Warehouse = 'A-3-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799388' WHERE Warehouse = 'P-B-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799389' WHERE Warehouse = 'E-5-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799390' WHERE Warehouse = 'P-G-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799391' WHERE Warehouse = 'A-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799392' WHERE Warehouse = 'CBB-88';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799393' WHERE Warehouse = 'E-5-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799394' WHERE Warehouse = 'E-8-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799395' WHERE Warehouse = 'P-E-5-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799396' WHERE Warehouse = 'H-1-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799397' WHERE Warehouse = 'P-E-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799398' WHERE Warehouse = 'G-3-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799399' WHERE Warehouse = 'G-3-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799400' WHERE Warehouse = 'C-4-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799401' WHERE Warehouse = 'D-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799402' WHERE Warehouse = 'P-E-8-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799403' WHERE Warehouse = 'B-4-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799404' WHERE Warehouse = 'CBB-22';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799405' WHERE Warehouse = 'E-2-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799406' WHERE Warehouse = 'E-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000010799407' WHERE Warehouse = 'CBB-47';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000011018421' WHERE Warehouse = 'CBB-101';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000011018422' WHERE Warehouse = 'CBB-102';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000011018423' WHERE Warehouse = 'CBB-04';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000011018424' WHERE Warehouse = 'P-E-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000011274426' WHERE Warehouse = 'P-B-5-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000011274427' WHERE Warehouse = 'P-B-5-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013861' WHERE Warehouse = 'G-1-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013862' WHERE Warehouse = 'CBB-103';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013863' WHERE Warehouse = 'D-5-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013864' WHERE Warehouse = 'P-A-4-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013865' WHERE Warehouse = 'P-B-1-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013866' WHERE Warehouse = 'E-4-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013867' WHERE Warehouse = 'E-D-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013868' WHERE Warehouse = 'C-2-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013869' WHERE Warehouse = 'P-G-5-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013870' WHERE Warehouse = 'F-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013871' WHERE Warehouse = 'E-5-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013872' WHERE Warehouse = 'E-D-30';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013873' WHERE Warehouse = 'G-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013874' WHERE Warehouse = 'C-1-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013875' WHERE Warehouse = 'A-2-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013876' WHERE Warehouse = 'E-1-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013877' WHERE Warehouse = 'P-B-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013878' WHERE Warehouse = 'G-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013879' WHERE Warehouse = 'I-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013880' WHERE Warehouse = 'P-G-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013881' WHERE Warehouse = 'E-3-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013982' WHERE Warehouse = 'D-5-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013983' WHERE Warehouse = 'A-4-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013984' WHERE Warehouse = 'P-G-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012013985' WHERE Warehouse = 'P-G-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012014427' WHERE Warehouse = 'C-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012022424' WHERE Warehouse = 'A-2-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012022425' WHERE Warehouse = 'A-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012022426' WHERE Warehouse = 'A-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012022427' WHERE Warehouse = 'A-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012030427' WHERE Warehouse = 'E-D-30';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012038425' WHERE Warehouse = 'P-B-4-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012038426' WHERE Warehouse = 'P-B-4-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012038427' WHERE Warehouse = 'P-B-5-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012046427' WHERE Warehouse = 'E-D-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012140424' WHERE Warehouse = 'A-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012140425' WHERE Warehouse = 'A-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012140426' WHERE Warehouse = 'A-2-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012140427' WHERE Warehouse = 'A-2-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012325426' WHERE Warehouse = 'CBB-77';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012325427' WHERE Warehouse = 'CBB-77';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562533' WHERE Warehouse = 'A-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562534' WHERE Warehouse = 'A-1-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562535' WHERE Warehouse = 'P-B-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562536' WHERE Warehouse = 'P-B-4-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562537' WHERE Warehouse = 'P-C-5-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562538' WHERE Warehouse = 'P-E-5-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562539' WHERE Warehouse = 'A-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562540' WHERE Warehouse = 'P-A-4-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562541' WHERE Warehouse = 'P-C-2-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562542' WHERE Warehouse = 'P-G-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562543' WHERE Warehouse = 'E-8-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562544' WHERE Warehouse = 'E-9-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562545' WHERE Warehouse = 'P-B-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562546' WHERE Warehouse = 'H-1-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562547' WHERE Warehouse = 'E-10-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562548' WHERE Warehouse = 'E-3-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562549' WHERE Warehouse = 'C-3-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562550' WHERE Warehouse = 'H-4-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562551' WHERE Warehouse = 'C-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562552' WHERE Warehouse = 'D-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562553' WHERE Warehouse = 'D-4-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562554' WHERE Warehouse = 'P-A-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562555' WHERE Warehouse = 'I-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562556' WHERE Warehouse = 'C-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562557' WHERE Warehouse = 'E-5-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562558' WHERE Warehouse = 'F-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562559' WHERE Warehouse = 'P-E-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562560' WHERE Warehouse = 'C-4-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562561' WHERE Warehouse = 'C-4-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562562' WHERE Warehouse = 'E-D-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562563' WHERE Warehouse = 'E-D-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562564' WHERE Warehouse = 'A-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562565' WHERE Warehouse = 'A-3-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562586' WHERE Warehouse = 'D-4-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562587' WHERE Warehouse = 'B-2-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562588' WHERE Warehouse = 'D-5-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562589' WHERE Warehouse = 'I-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562590' WHERE Warehouse = 'I-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562591' WHERE Warehouse = 'P-A-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562592' WHERE Warehouse = 'P-E-10-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562593' WHERE Warehouse = 'A-3-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562594' WHERE Warehouse = 'B-3-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562595' WHERE Warehouse = 'D-5-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562596' WHERE Warehouse = 'B-5-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012562597' WHERE Warehouse = 'P-A-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642416' WHERE Warehouse = 'B-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642417' WHERE Warehouse = 'B-2-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642418' WHERE Warehouse = 'B-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642419' WHERE Warehouse = 'B-2-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642420' WHERE Warehouse = 'B-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642421' WHERE Warehouse = 'B-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642422' WHERE Warehouse = 'B-2-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642423' WHERE Warehouse = 'B-2-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642424' WHERE Warehouse = 'B-2-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642425' WHERE Warehouse = 'B-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642426' WHERE Warehouse = 'B-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012642427' WHERE Warehouse = 'B-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645827' WHERE Warehouse = 'P-I-4-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645828' WHERE Warehouse = 'E-8-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645829' WHERE Warehouse = 'E-2-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645830' WHERE Warehouse = 'I-3-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645831' WHERE Warehouse = 'B-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645832' WHERE Warehouse = 'P-E-1-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645833' WHERE Warehouse = 'E-2-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645834' WHERE Warehouse = 'P-F-2-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645835' WHERE Warehouse = 'F-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645836' WHERE Warehouse = 'F-3-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645837' WHERE Warehouse = 'B-3-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645838' WHERE Warehouse = 'B-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645839' WHERE Warehouse = 'B-5-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645840' WHERE Warehouse = 'E-5-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645841' WHERE Warehouse = 'E-9-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645842' WHERE Warehouse = 'F-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645843' WHERE Warehouse = 'P-D-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645844' WHERE Warehouse = 'B-5-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645845' WHERE Warehouse = 'P-F-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645846' WHERE Warehouse = 'H-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012645847' WHERE Warehouse = 'P-F-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012646119' WHERE Warehouse = 'A-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012646124' WHERE Warehouse = 'C-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012646125' WHERE Warehouse = 'C-4-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012646427' WHERE Warehouse = 'P-B-5-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648396' WHERE Warehouse = 'C-1-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648397' WHERE Warehouse = 'C-1-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648398' WHERE Warehouse = 'C-1-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648399' WHERE Warehouse = 'C-1-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648400' WHERE Warehouse = 'C-1-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648401' WHERE Warehouse = 'C-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648402' WHERE Warehouse = 'C-2-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648403' WHERE Warehouse = 'C-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648404' WHERE Warehouse = 'C-2-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648405' WHERE Warehouse = 'C-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648406' WHERE Warehouse = 'C-2-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648407' WHERE Warehouse = 'C-2-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648408' WHERE Warehouse = 'C-2-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648409' WHERE Warehouse = 'C-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648410' WHERE Warehouse = 'C-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648411' WHERE Warehouse = 'C-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648412' WHERE Warehouse = 'C-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648413' WHERE Warehouse = 'C-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648414' WHERE Warehouse = 'C-3-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648415' WHERE Warehouse = 'C-3-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648416' WHERE Warehouse = 'C-3-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648417' WHERE Warehouse = 'C-3-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648418' WHERE Warehouse = 'C-4-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648419' WHERE Warehouse = 'C-4-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648420' WHERE Warehouse = 'C-4-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648421' WHERE Warehouse = 'C-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648422' WHERE Warehouse = 'C-4-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648423' WHERE Warehouse = 'C-4-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648424' WHERE Warehouse = 'C-4-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648425' WHERE Warehouse = 'C-4-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648426' WHERE Warehouse = 'C-4-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012648427' WHERE Warehouse = 'C-2-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649415' WHERE Warehouse = 'A-2-18';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649416' WHERE Warehouse = 'A-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649417' WHERE Warehouse = 'A-3-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649418' WHERE Warehouse = 'A-3-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649419' WHERE Warehouse = 'A-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649420' WHERE Warehouse = 'A-4-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649421' WHERE Warehouse = 'A-4-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649422' WHERE Warehouse = 'A-4-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649423' WHERE Warehouse = 'A-4-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649424' WHERE Warehouse = 'A-4-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649425' WHERE Warehouse = 'A-4-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649426' WHERE Warehouse = 'A-4-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012649427' WHERE Warehouse = 'A-4-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665353' WHERE Warehouse = 'P-E-6-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665354' WHERE Warehouse = 'D-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665355' WHERE Warehouse = 'P-D-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665356' WHERE Warehouse = 'E-9-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665357' WHERE Warehouse = 'H-4-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665358' WHERE Warehouse = 'P-I-5-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665359' WHERE Warehouse = 'B-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665360' WHERE Warehouse = 'B-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665361' WHERE Warehouse = 'B-3-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665362' WHERE Warehouse = 'B-3-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665363' WHERE Warehouse = 'B-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665364' WHERE Warehouse = 'B-4-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665365' WHERE Warehouse = 'B-4-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665366' WHERE Warehouse = 'B-4-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665367' WHERE Warehouse = 'B-4-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665368' WHERE Warehouse = 'B-4-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665369' WHERE Warehouse = 'B-4-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665370' WHERE Warehouse = 'B-4-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665371' WHERE Warehouse = 'B-5-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665372' WHERE Warehouse = 'B-5-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665373' WHERE Warehouse = 'B-5-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665374' WHERE Warehouse = 'B-5-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665375' WHERE Warehouse = 'B-5-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665376' WHERE Warehouse = 'B-5-12';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665377' WHERE Warehouse = 'B-5-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665378' WHERE Warehouse = 'B-5-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665379' WHERE Warehouse = 'B-5-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665380' WHERE Warehouse = 'B-5-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665381' WHERE Warehouse = 'B-5-19';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665382' WHERE Warehouse = 'P-C-5-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665383' WHERE Warehouse = 'P-E-9-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665384' WHERE Warehouse = 'E-8-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665385' WHERE Warehouse = 'H-2-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665386' WHERE Warehouse = 'P-F-5-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665387' WHERE Warehouse = 'I-1-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665388' WHERE Warehouse = 'G-3-14';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665389' WHERE Warehouse = 'P-E-8-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665390' WHERE Warehouse = 'E-1-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665391' WHERE Warehouse = 'E-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665392' WHERE Warehouse = 'E-D-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665393' WHERE Warehouse = 'E-D-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665394' WHERE Warehouse = 'P-E-5-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665414' WHERE Warehouse = 'P-C-1-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665415' WHERE Warehouse = 'P-C-1-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665416' WHERE Warehouse = 'P-C-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665417' WHERE Warehouse = 'P-C-1-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665418' WHERE Warehouse = 'P-C-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665419' WHERE Warehouse = 'P-C-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665420' WHERE Warehouse = 'P-C-2-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665421' WHERE Warehouse = 'P-C-2-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665422' WHERE Warehouse = 'P-C-3-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665423' WHERE Warehouse = 'P-C-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665424' WHERE Warehouse = 'P-C-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665425' WHERE Warehouse = 'P-C-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665426' WHERE Warehouse = 'P-C-3-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012665427' WHERE Warehouse = 'P-C-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672417' WHERE Warehouse = 'P-B-3-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672418' WHERE Warehouse = 'P-B-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672419' WHERE Warehouse = 'P-B-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672420' WHERE Warehouse = 'P-B-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672421' WHERE Warehouse = 'P-B-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672422' WHERE Warehouse = 'P-B-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672423' WHERE Warehouse = 'P-B-4-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672424' WHERE Warehouse = 'P-B-4-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672425' WHERE Warehouse = 'P-B-4-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672426' WHERE Warehouse = 'P-B-4-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012672427' WHERE Warehouse = 'P-B-4-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012687421' WHERE Warehouse = 'A-1-13';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012687422' WHERE Warehouse = 'A-1-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012687423' WHERE Warehouse = 'A-1-16';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012687424' WHERE Warehouse = 'A-1-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012687425' WHERE Warehouse = 'A-1-20';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012687426' WHERE Warehouse = 'A-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012687427' WHERE Warehouse = 'A-2-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692384' WHERE Warehouse = 'P-A-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692385' WHERE Warehouse = 'P-A-1-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692386' WHERE Warehouse = 'P-A-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692387' WHERE Warehouse = 'P-A-1-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692388' WHERE Warehouse = 'P-A-2-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692389' WHERE Warehouse = 'P-A-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692390' WHERE Warehouse = 'P-A-2-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692391' WHERE Warehouse = 'P-A-2-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692392' WHERE Warehouse = 'P-A-3-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692393' WHERE Warehouse = 'P-A-3-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692394' WHERE Warehouse = 'P-A-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692395' WHERE Warehouse = 'P-A-3-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692396' WHERE Warehouse = 'P-A-3-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692397' WHERE Warehouse = 'P-A-3-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692398' WHERE Warehouse = 'P-A-4-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692399' WHERE Warehouse = 'P-A-4-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692400' WHERE Warehouse = 'P-A-5-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692401' WHERE Warehouse = 'P-A-5-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692402' WHERE Warehouse = 'P-A-5-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692403' WHERE Warehouse = 'P-A-5-9';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692404' WHERE Warehouse = 'G-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692405' WHERE Warehouse = 'H-3-5';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692406' WHERE Warehouse = 'P-E-5-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692407' WHERE Warehouse = 'C-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692408' WHERE Warehouse = 'B-5-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692409' WHERE Warehouse = 'E-1-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692410' WHERE Warehouse = 'E-4-17';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692411' WHERE Warehouse = 'D-4-15';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692412' WHERE Warehouse = 'G-3-7';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692413' WHERE Warehouse = 'P-E-3-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692414' WHERE Warehouse = 'P-C-1-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692415' WHERE Warehouse = 'E-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692416' WHERE Warehouse = 'P-B-1-1';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692417' WHERE Warehouse = 'P-A-5-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692418' WHERE Warehouse = 'P-B-1-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692419' WHERE Warehouse = 'P-B-1-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692420' WHERE Warehouse = 'P-B-1-10';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692421' WHERE Warehouse = 'P-B-1-11';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692422' WHERE Warehouse = 'P-B-2-2';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692423' WHERE Warehouse = 'P-B-2-3';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692424' WHERE Warehouse = 'P-B-2-4';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692425' WHERE Warehouse = 'P-B-2-6';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692426' WHERE Warehouse = 'P-B-2-8';\n";
        echo "UPDATE CLUBS_LOTS SET ACTIVE = 1, DIM = '11000012692427' WHERE Warehouse = 'P-B-2-9';\n";
    }

    public function scapeCharacters($string)
    {
        return str_replace("'", "''", $string);
    }

    public function likeName($string)
    {
        return str_replace(
            ['¿', '?', '"', '”', ' ', ',', '-', '·', "'", '’', 'á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä', 'é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë', 'í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î', 'ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô', 'ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü', 'ñ', 'Ñ', 'ç', 'Ç'],
            '%',
            $string
        );
    }
}
