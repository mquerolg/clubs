<?php

namespace App\Command;

use App\Entity\Lots;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateLotsCopiesCommand extends Command
{
    protected static $defaultName = 'UpdateCopies';
    protected static $defaultDescription = 'Update Copies';

    private $em;
    private $doctrine;

    public function __construct(EntityManagerInterface $em, ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->em = $em;
        $this->doctrine = $doctrine;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->updateCodesCopies();

            $io->success('FINISHED');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e);

            return Command::FAILURE;
        }
    }

    /**
     * Obtiene el valor de los códigos bibliograficos
     */
    protected function updateCodesCopies()
    {
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
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }

        $codes = $this->getCodesFromLots();

        foreach ($codes as $value) {
            $codiBibliographic = substr($value->getBibliographic(), 1, -1);
            $lotId = $value->getId();

            if ($value->getBibliographic() != '' && $this->isLongType($value->getBibliographic())) {
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
                    "
                ;

                $stmt = $pdo->prepare($sql);
                $stmt->execute(['num' => $codiBibliographic]);
                $code = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($code)) {
                    $j = 0;
                    foreach ($code as $key => $valueCode) {
                        $conn = $this->em->getConnection()->getNativeConnection();

                        oci_execute(oci_parse($conn, 'SET ROLE ALL'));
                        $checkQuery = "SELECT COUNT(*) as count FROM CLUBS_COPIES WHERE lot_id = '" . $lotId . "' and name = '" . $valueCode . "'";
                        $stid = oci_parse($conn, $checkQuery);

                        if (!oci_execute($stid)) {
                            throw new \Exception('Error al ejecutar la consulta SELECT');
                        }

                        $rows = [];

                        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                            $rows[] = $row;
                        }

                        $count = $rows[0]['COUNT'];

                        oci_execute(oci_parse($conn, 'SET ROLE ALL'));
                        $checkQuery = "SELECT COUNT(*) as count FROM CLUBS_LOTS WHERE id = '" . $lotId . "' and exemplar = '" . $valueCode . "'";
                        $stid = oci_parse($conn, $checkQuery);

                        if (!oci_execute($stid)) {
                            throw new \Exception('Error al ejecutar la consulta SELECT');
                        }

                        $rows = [];
                        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                            $rows[] = $row;
                        }

                        $count2 = $rows[0]['COUNT'];

                        if ($j == 0 && $count2 == 0) {
                            $updateQuery = "UPDATE CLUBS_LOTS SET exemplar = '" . $valueCode . "' WHERE ID = '" . $lotId . "'";

                            $stid = oci_parse($conn, $updateQuery);

                            if (!oci_execute($stid)) {
                                throw new \Exception('Error al ejecutar la consulta UPDATE');
                            }
                        }
                        if ($count == 0 && $count2 == 0 && $j > 0) {
                            // El valor no existe, realizar la inserción
                            $insertQuery = "INSERT INTO CLUBS_COPIES (id, name, lot_id) VALUES (CLUBS_COPIES_SEQ.NEXTVAL,'" . $valueCode . "','" . $lotId . "')";

                            $stid = oci_parse($conn, $insertQuery);

                            if (!oci_execute($stid)) {
                                throw new \Exception('Error al ejecutar la consulta UPDATE');
                            }
                        }
                        $j++;
                    }
                }
            }
        }
    }

    /**
     * sendFtpDeletedLots
     *
     * @return void
     */
    public function getCodesFromLots()
    {
        $entityManager = $this->doctrine->getManagerForClass(Lots::class);

        $lots = $entityManager->createQueryBuilder()
          ->select('entity')->from(Lots::class, 'entity')
          ->andWhere('entity.bibliographic IS NOT NULL')
          ->orderBy('entity.id', 'DESC')
          ->getQuery()->getResult();

        return $lots;
    }

    protected function isLongType($str)
    {
        // Verifica si la cadena empieza con "b" y termina con un número o "x".
        return preg_match('/^b[0-9]+[0-9x]$/', $str) ? true : false;
    }
}
