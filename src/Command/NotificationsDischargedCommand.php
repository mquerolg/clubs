<?php

namespace App\Command;

use App\Diba\Helpers\FtpFileHelper as FtpHelper;
use App\Entity\Lots;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotificationsDischargedCommand extends Command
{
    protected static $defaultName = 'NotificationsDischarged';

    private $em;
    private $doctrine;

    public function __construct(EntityManagerInterface $em, ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->em = $em;
        $this->doctrine = $doctrine;
    }

    /**
     * sendFtpDischargedLots
     */
    public function sendFtpDischargedLots(): void
    {
        $current_data = new \DateTime('now');
        $previous_data = new \DateTime('now');

        $previous_data->sub(new \DateInterval('PT1H'));

        $entityManager = $this->doctrine->getManagerForClass(Lots::class);

        $previous_data->setTimezone(new DateTimeZone('Europe/Madrid'));
        $current_data->setTimezone(new DateTimeZone('Europe/Madrid'));

        $lots = $entityManager->createQueryBuilder()
            ->select('entity')->from(Lots::class, 'entity')
            ->andWhere('entity.createdAt BETWEEN :previous AND :current')
            ->setParameter('previous', $previous_data->format('d-M-y h.i.s.u A'))
            ->setParameter('current', $current_data->format('d-M-y h.i.s.u A'))
            ->orderBy('entity.id', 'DESC')
            ->getQuery()->getResult();

        $this->sendFtpFileDischargedLots($lots, $current_data);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->sendFtpDischargedLots();

            $io->success('FINISHED');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Prepare file for send with ftp connection of discharged lots
     *
     * @param  mixed $lots
     * @param  mixed $current_data
     */
    private function sendFtpFileDischargedLots($lots, $current_data): void
    {
        $dateFormated = $current_data->format('YmdHi');
        $fileName = 'Altalot_' . $dateFormated . '.txt';
        $dataArray = [];

        foreach ($lots as $value) {
            $langValue = implode('_', array_filter([
                $value->getLangCat() > 0 ? 'CAT' : '',
                $value->getLangEs() > 0 ? 'ES' : '',
                $value->getLangAng() > 0 ? 'ANG' : '',
                $value->getLangFra() > 0 ? 'FRA' : '',
                $value->getLangIta() > 0 ? 'ITA' : '',
                $value->getLangAle() > 0 ? 'ALE' : '',
            ]));

            $checkValue = !empty($langValue) ? $langValue . '_' : '';

            $dataArray[] = $value->getTitle() . '/ ' .
                            $value->getAuthorship() . '|' .
                            $value->getDim() . '|' .
                            $value->getWarehouse() . '|' .
                            $langValue . '|' .
                            $value->getLangSum() . ' EX|cb_' .
                            $value->getTitle() . '/' .
                            $value->getAuthorship() . '_' .
                            $value->getLangSum() . ' EX_' .
                            $value->getWarehouse() . '_' .
                            $checkValue . '1/1';
        }

        FtpHelper::csvFtpMaker($dataArray, $fileName, 'Texto breve|codi dim|codi magatzem|Idioma|Exemplar|Texto ampliado');
    }
}
