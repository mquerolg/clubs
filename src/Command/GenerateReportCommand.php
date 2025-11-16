<?php

namespace App\Command;

use App\Entity\Reports\ClubsReport;
use App\Entity\Reports\LotsReport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateReportCommand extends Command
{
    protected static $defaultName = 'GenerateReport';
    protected static $defaultDescription = 'Update Report tables';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $year = date('Y');
        $conn = $this->em->getConnection()->getNativeConnection();

        if (!$conn) {
            return 0;
        }
        
        oci_execute(oci_parse($conn, 'SET ROLE ALL'));

        $this->updateLots($conn, $year);
        $this->updateClubs($conn, $year);
        $this->updateGenres($conn, $year);
        $this->updateMunicipality($conn, $year);
        $this->updateZone($conn, $year);
        $this->updateClubsLots($conn, $year);

        $io->success('FINISHED');

        return Command::SUCCESS;
    }

    protected function updateLots($conn, $year): void
    {
        $sql = "SELECT
                    Y.ID AS YEAR,
                    count(distinct L.ID) AS CREATED,
                    count(distinct D.ID) AS DISCHARGED,
                    count(distinct H.ID) AS BORROWED
                FROM (SELECT {$year} AS ID from dual) Y
                LEFT JOIN CLUBS_LOTS L ON TO_CHAR(L.created_at, 'YYYY') = Y.ID
                LEFT JOIN CLUBS_LOTS D ON TO_CHAR(D.deleted_At, 'YYYY') = Y.ID
                LEFT JOIN CLUBS_HISTORIC H ON TO_CHAR(H.created_at, 'YYYY') = Y.ID
                GROUP BY Y.ID";

        $stid = oci_parse($conn, $sql);

        oci_execute($stid);

        $result = $this->em->getRepository(LotsReport::class)->findBy(['year' => $year]);
        $pass = $this->em->getRepository(LotsReport::class)->findBy(['year' => $year - 1])[0];

        $total = $pass->getTotal() - $pass->getDischarged();

        if (empty($result)) {
            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                $query = "INSERT INTO CLUBS_REPORT_LOTS (ID,YEAR,CREATED,DISCHARGED,BORROWED,TOTAL) 
                          VALUES (CLUBS_REPORT_LOTS_SEQ.NEXTVAL,'" . $year . "','" . $row['CREATED'] . "','" . $row['DISCHARGED'] . "','" . $row['BORROWED'] . "','" . (int) ((int) $total + (int) $row['CREATED']) . "')";

                $stid = oci_parse($conn, $query);

                oci_execute($stid);

                break;
            }
        } else {
            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                $query = "UPDATE CLUBS_REPORT_LOTS
                          SET CREATED = '" . $row['CREATED'] . "', DISCHARGED = '" . $row['DISCHARGED'] . "', BORROWED  = '" . $row['BORROWED'] . "', TOTAL = '" . (int) ((int) $total + (int) $row['CREATED']) . "'
                          WHERE YEAR = '" . $year . "'";

                $stid = oci_parse($conn, $query);

                oci_execute($stid);

                break;
            }
        }
    }

    protected function updateClubs($conn, $year): void
    {
        $sql = "SELECT
                    Y.ID AS YEAR,
                    count(distinct L.ID) AS CREATED,
                    count(distinct D.ID) AS DISCHARGED
                 FROM (SELECT {$year} AS ID from dual) Y
                 LEFT JOIN CLUBS_CLUBS L ON TO_CHAR(L.created_at, 'YYYY') = Y.ID
                 LEFT JOIN CLUBS_CLUBS D ON TO_CHAR(D.deleted_At, 'YYYY') = Y.ID
                 GROUP BY Y.ID";

        $stid = oci_parse($conn, $sql);

        oci_execute($stid);

        $result = $this->em->getRepository(ClubsReport::class)->findBy(['year' => $year]);
        $pass = $this->em->getRepository(ClubsReport::class)->findBy(['year' => $year - 1])[0];

        $total = $pass->getTotal() - $pass->getDischarged();

        if (empty($result)) {
            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                $query = "INSERT INTO CLUBS_REPORT_CLUBS (ID,YEAR,CREATED,DISCHARGED,TOTAL) 
                          VALUES (CLUBS_REPORT_CLUBS_SEQ.NEXTVAL,'" . $year . "','" . $row['CREATED'] . "','" . $row['DISCHARGED'] . "','" . (int) ((int) $total + (int) $row['CREATED']) . "')";

                $stid = oci_parse($conn, $query);

                oci_execute($stid);

                break;
            }
        } else {
            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                $query = "UPDATE CLUBS_REPORT_CLUBS
                          SET CREATED = '" . $row['CREATED'] . "', DISCHARGED  = '" . $row['DISCHARGED'] . "', TOTAL = '" . (int) ((int) $total + (int) $row['CREATED']) . "'
                          WHERE YEAR = '" . $year . "'";

                $stid = oci_parse($conn, $query);

                oci_execute($stid);

                break;
            }
        }
    }

    protected function updateGenres($conn, $year): void
    {
        $stid = oci_parse($conn, "DELETE FROM CLUBS_REPORT_GENRES WHERE YEAR = {$year}");

        oci_execute($stid);

        $stid = oci_parse($conn, "UPDATE CLUBS_LIBRARIES SET ZONE = 'Vallès Occidental' WHERE ZONE = 'Valles Occidental'");

        oci_execute($stid);

        $sql = "SELECT
                    Y.ID AS YEAR,
                    G.NAME AS GENRE,
                    C.NAME AS CLUB,
                    B.NAME AS LIBRARY,
                    count(distinct H.ID) AS TOTAL
                 FROM (SELECT {$year} AS ID from dual) Y
                 LEFT JOIN CLUBS_HISTORIC H ON TO_CHAR(H.created_at, 'YYYY') = Y.ID
                 LEFT JOIN CLUBS_LOTS L ON L.ID = H.LOT_ID
                 LEFT JOIN CLUBS_GENRES G ON G.ID = L.GENRE_ID
                 LEFT JOIN CLUBS_CLUBS C ON C.ID = H.CLUB_ID
                 LEFT JOIN CLUBS_LIBRARIES B ON B.ID = H.LIBRARY_ID
                 GROUP BY Y.ID, G.NAME, C.NAME, B.NAME
                 ORDER BY YEAR, GENRE, LIBRARY, CLUB, TOTAL";

        $stid = oci_parse($conn, $sql);

        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $queries[] = "INSERT INTO CLUBS_REPORT_GENRES (ID,YEAR,GENRE,CLUB,LIBRARY,TOTAL) 
                          VALUES (CLUBS_REPORT_GENRES_SEQ.NEXTVAL,'" . $year . "','" . str_replace("'", "''", $row['GENRE']) . "','" . str_replace("'", "''", $row['CLUB']) . "','" . str_replace("'", "''", $row['LIBRARY']) . "','" . $row['TOTAL'] . "')";
        }

        foreach ($queries as $query) {
            $stid = oci_parse($conn, $query);

            oci_execute($stid);
        }
    }

    protected function updateMunicipality($conn, $year): void
    {
        $stid = oci_parse($conn, "DELETE FROM CLUBS_REPORT_MUNICIPALITY WHERE YEAR = {$year}");

        oci_execute($stid);

        $sql = "SELECT 
                    Y.ID AS YEAR,
                    B.MUNICIPALITY AS MUNICIPALITY,
                    count(distinct C.ID) AS CLUBS,
                    count(distinct H.ID) AS LOTS
                 FROM (SELECT {$year} AS ID from dual) Y
                 LEFT JOIN CLUBS_HISTORIC H ON TO_CHAR(H.created_at, 'YYYY') = Y.ID
                 LEFT JOIN CLUBS_LIBRARIES B ON B.ID = H.LIBRARY_ID
                 LEFT JOIN CLUBS_CLUBS C ON B.ID = C.LIBRARY_ID
                 GROUP BY Y.ID, B.MUNICIPALITY
                 ORDER BY YEAR, MUNICIPALITY";

        $stid = oci_parse($conn, $sql);

        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $queries[] = "INSERT INTO CLUBS_REPORT_MUNICIPALITY (ID,YEAR,MUNICIPALITY,CLUBS,LOTS) 
                          VALUES (CLUBS_REPORT_MUNICIPALITY_SEQ.NEXTVAL,'" . $year . "','" . str_replace("'", "''", $row['MUNICIPALITY']) . "','" . $row['CLUBS'] . "','" . $row['LOTS'] . "')";
        }

        foreach ($queries as $query) {
            $stid = oci_parse($conn, $query);

            oci_execute($stid);
        }
    }

    protected function updateZone($conn, $year): void
    {
        $stid = oci_parse($conn, "DELETE FROM CLUBS_REPORT_ZONE WHERE YEAR = {$year}");

        oci_execute($stid);

        $stid = oci_parse($conn, "UPDATE CLUBS_LIBRARIES SET ZONE = 'Vallès Occidental' WHERE ZONE = 'Valles Occidental'");

        oci_execute($stid);

        $sql = "SELECT 
                    M.YEAR AS YEAR,
                    L.ZONE AS ZONE,
                    SUM(M.CLUBS) AS CLUBS,
                    SUM(M.LOTS) AS LOTS
                 FROM (SELECT B.ZONE, B.MUNICIPALITY FROM CLUBS_LIBRARIES B GROUP BY B.ZONE, B.MUNICIPALITY) L
                 JOIN CLUBS_REPORT_MUNICIPALITY M ON M.MUNICIPALITY = L.MUNICIPALITY
                 WHERE M.YEAR = {$year}
                 GROUP BY M.YEAR, L.ZONE
                 ORDER BY YEAR";

        $stid = oci_parse($conn, $sql);

        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $queries[] = "INSERT INTO CLUBS_REPORT_ZONE (ID,YEAR,ZONE,CLUBS,LOTS) 
                          VALUES (CLUBS_REPORT_ZONE_SEQ.NEXTVAL,'" . $year . "','" . str_replace("'", "''", $row['ZONE']) . "','" . $row['CLUBS'] . "','" . $row['LOTS'] . "')";
        }

        foreach ($queries as $query) {
            $stid = oci_parse($conn, $query);

            oci_execute($stid);
        }
    }

    protected function updateClubsLots($conn, $year): void
    {
        $stid = oci_parse($conn, "DELETE FROM CLUBS_REPORT_CLUBS_LOTS WHERE YEAR = {$year}");

        oci_execute($stid);

        $stid = oci_parse($conn, "UPDATE CLUBS_LIBRARIES SET ZONE = 'Vallès Occidental' WHERE ZONE = 'Valles Occidental'");

        oci_execute($stid);

        $sql = "SELECT 
                     C.NAME AS CLUB,
                     L.NAME AS LIBRARY,
                     L.MUNICIPALITY AS MUNICIPALITY,
                     L.ZONE AS ZONE,
                     count(h.id) AS LOTS
                 FROM CLUBS_CLUBS C
                 LEFT JOIN CLUBS_LIBRARIES L ON L.ID = C.LIBRARY_ID
                 LEFT JOIN CLUBS_HISTORIC H ON C.ID = H.CLUB_ID AND {$year} = TO_CHAR(H.CREATED_AT, 'YYYY')
                 GROUP BY C.NAME, L.NAME, L.MUNICIPALITY, L.ZONE";

        $stid = oci_parse($conn, $sql);

        oci_execute($stid);

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $queries[] = "INSERT INTO CLUBS_REPORT_CLUBS_LOTS (ID,YEAR,CLUB,LIBRARY,MUNICIPALITY,ZONE,LOTS)
                          VALUES (CLUBS_REPORT_CLUBS_LOTS_SEQ.NEXTVAL,'" . $year . "','" . str_replace("'", "''", $row['CLUB']) . "','" . str_replace("'", "''", $row['LIBRARY']) . "','" . str_replace("'", "''", $row['MUNICIPALITY']) . "','" . str_replace("'", "''", $row['ZONE']) . "','" . $row['LOTS'] . "')";
        }

        foreach ($queries as $query) {
            $stid = oci_parse($conn, $query);

            oci_execute($stid);
        }
    }
}
