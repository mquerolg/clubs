<?php

namespace App\Command;

use App\Diba\Helpers\FtpFileHelper as FtpHelper;
use App\Entity\Deleted\LotsDeleted;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotificationsDeletedCommand extends Command
{
    protected static $defaultName = 'NotificationsDeleted';
    protected static $defaultDescription = 'Send Notifications Deleted Lots';

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
            $this->sendFtpDeletedLots();

            $io->success('FINISHED');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * sendFtpDeletedLots
     *
     * @return void
     */
    public function sendFtpDeletedLots()
    {
        $current_data = new \DateTime('now');
        $previous_data = new \DateTime('now');

        $previous_data->sub(new \DateInterval('PT1H'));

        $entityManager = $this->doctrine->getManagerForClass(LotsDeleted::class);

        $previous_data->setTimezone(new DateTimeZone('Europe/Madrid'));
        $current_data->setTimezone(new DateTimeZone('Europe/Madrid'));

        $lots = $entityManager->createQueryBuilder()
          ->select('entity')->from(LotsDeleted::class, 'entity')
          ->andWhere('entity.deletedAt BETWEEN :previous AND :current')
          ->setParameter('previous', $previous_data->format('d-M-y h.i.s.u A'))
          ->setParameter('current', $current_data->format('d-M-y h.i.s.u A'))
          ->orderBy('entity.id', 'DESC')
          ->getQuery()->getResult();

        $this->sendFtpFileDeletedLots($lots, $current_data);
    }

    /**
     * Prepare file for send with ftp connection of deleted lots
     *
     * @param  mixed $lots
     * @param  mixed $current_data
     * @return void
     */
    private function sendFtpFileDeletedLots($lots, $current_data)
    {
        $current_data->setTimezone(new DateTimeZone('Europe/Madrid'));
        $dateFormated = $current_data->format('YmdHi');
        $fileName = 'Baixalot_' . $dateFormated . '.txt';
        $dataArray = [];

        foreach ($lots as $value) {
            $dataArray[] = $value->getWarehouse();
        }

        FtpHelper::csvFtpMaker($dataArray, $fileName, 'Codi Magatzem');
    }
}
