<?php

namespace App\Command;

use App\Diba\Helpers\FtpFileHelper as FtpHelper;
use App\Diba\SamcService;
use App\Entity\Historic;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Diba\Helpers\StateHelper as State;

class NotificationsCommand extends Command
{
    /**
     * const
     *
     * Containt the number of days in interval to send mail
     */
    public const DAYS_PERIOD_TO_SEND_MAIL = 15;

    protected static $defaultName = 'Notifications';
    protected static $defaultDescription = 'Send Notifications';

    private $em;
    private $doctrine;

    public function __construct(EntityManagerInterface $em, ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->em = $em;
        $this->doctrine = $doctrine;
    }

    /**
     * sendSapEmail
     *
     * @param  mixed $conn
     */
    public function sendSapEmail(): void
    {
        $current_data = new \DateTime('now');
        $previous_data = new \DateTime('now');

        $previous_data->sub(new \DateInterval('P1D'));

        $entityManager = $this->doctrine->getManagerForClass(Historic::class);

        $lots = $entityManager->createQueryBuilder()
            ->select('entity')->from(Historic::class, 'entity')
            ->join('entity.lot', 'lot')
            ->andWhere('entity.closedAt IS NULL')
            ->andWhere('entity.createdAt BETWEEN :previous AND :current')
            ->andWhere('lot.statusId = ' . State::REQUESTED)
            ->setParameter('previous', $previous_data->format('d-M-y h.i.s.u A'))
            ->setParameter('current', $current_data->format('d-M-y h.i.s.u A'))
            ->orderBy('entity.id', 'DESC')
            ->getQuery()->getResult();

        if (!empty($lots)) {
            SamcService::cronMessage($lots);
        }

        $this->sendFtpFileRequestLot($lots, $current_data);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $conn = $this->em->getConnection()->getNativeConnection();

            if (!$conn) {
                throw new \Exception('No se puede conectar a la base de datos');
            }

            oci_execute(oci_parse($conn, 'SET ROLE ALL'));

            $stid = oci_parse($conn, 'SELECT ch.*, cl.EMAIL, cl.CODE, clot.AUTHORSHIP, clot.WAREHOUSE, clot.TITLE
                                            FROM CLUBS_HISTORIC ch
                                            JOIN CLUBS_LIBRARIES cl ON cl.ID = ch.LIBRARY_ID 
                                            JOIN CLUBS_LOTS clot ON clot.ID = ch.LOT_ID
                                        WHERE CLOSED_AT IS NULL 
                                            AND RETURNED_AT IS NULL
                                            AND RECEIVED_AT IS NOT NULL
                                            AND (RETURN_IN + (EXCEDED * ' . self::DAYS_PERIOD_TO_SEND_MAIL . ')) < CURRENT_TIMESTAMP ');

            if (!oci_execute($stid)) {
                throw new \Exception('Error al ejecutar la consulta SELECT');
            }

            $rows = [];

            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                $rows[] = $row;
            }

            foreach ($rows as $row) {
                $query = 'UPDATE CLUBS_HISTORIC SET EXCEDED = \'' . ++$row['EXCEDED'] . '\'WHERE ID = \'' . $row['ID'] . '\'';

                $stid = oci_parse($conn, $query);

                if (!oci_execute($stid)) {
                    throw new \Exception('Error al ejecutar la consulta UPDATE');
                }

                $lot_query = oci_parse($conn, 'UPDATE CLUBS_LOTS SET STATUS_ID = ' . State::IS_RETURN . ' WHERE ID = ' . $row['LOT_ID'] . '');

                if (!oci_execute($lot_query)) {
                    throw new \Exception('Error al ejecutar la consulta UPDATE');
                }

                SamcService::reminderLotsReturn(
                    $row['SEND_ID'],
                    $row['EMAIL'],
                    $row['CODE'],
                    $row['AUTHORSHIP'],
                    $row['WAREHOUSE'],
                    $row['TITLE']
                );
            }

            $this->sendSapEmail();

            $this->notificationResrvedLots($conn);

            $io->success('FINISHED');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    private function notificationResrvedLots($conn): void
    {
        oci_execute(oci_parse($conn, 'SET ROLE ALL'));

        $stid = oci_parse($conn, 'select * from clubs_lots l
        left join clubs_historic h on h.lot_id = l.id
        where status_id = 2
        AND closed_at >= (SELECT SYSDATE - 1 FROM dual)
        ');

        if (!oci_execute($stid)) {
            throw new \Exception('Error al ejecutar la consulta SELECT');
        }

        $rows = [];

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $rows[] = $row;
        }

        if (!empty($rows)) {
            foreach ($rows as $row) {
                SamcService::notifyAdminLotReserved($rows);
            }
        }
    }

    /**
     * Prepare file for send with ftp connection
     *
     * @param  mixed $lots
     * @param  mixed $date
     */
    private function sendFtpFileRequestLot($lots, $date): void
    {
        $dateFormated = $date->format('Ydm');
        $fileName = 'graella_' . $dateFormated . '.txt';
        $dataArray = [];

        foreach ($lots as $value) {
            $dataArray[] = $value->getLot()->getWarehouse() . '|' . $value->getLibrary()->getCode();
        }

        FtpHelper::csvFtpMaker($dataArray, $fileName, 'Mat|Bib');
    }
}
