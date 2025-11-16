<?php

namespace App\Diba\Helpers;

use App\Diba\DibaApi;
use App\Entity\Localizations;
use App\Entity\Support\UpdateLibraries as Libraries;

class CronHelper
{
    /**
     * const
     *
     * Containt the number of days in interval to send mail
     */
    public const DAYS_PERIOD_TO_SEND_MAIL = 15;

    /*
    |--------------------------------------------------------------------------
    | UPDATE FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * updateLibraries
     *
     * @param  mixed $container
     *
     * @return void
     */
    public static function updateLibraries($container)
    {
        $api = new DibaApi();

        foreach ($api->getLibraries() as $library) {
            self::updateLibrary($container, $library);
        }
    }

    /**
     * updateLibrary
     *
     * @param  mixed $container
     * @param  mixed $library
     *
     * @return void
     */
    protected static function updateLibrary($container, $library)
    {
        $entityManager = $container->get('doctrine')->getManagerForClass(Libraries::class);

        $result = $container->get('doctrine')->getRepository(Libraries::class)->findBy(['idDiba' => $library->getId()]);
        $global = $container->get('doctrine')->getRepository(Libraries::class)->findBy(['code' => $library->getGlobal()]);
        $active = $container->get('doctrine')->getRepository(Localizations::class)->findBy(['id' => $library->getGlobal()]);

        if (empty($result) && empty($global)) {
            $item = new Libraries();

            if (!empty($active[0]) && $active[0]->getActive() == 1) {
                $item->setActive(1);
            } else {
                $item->setActive(0);
            }
        } elseif (empty($result)) {
            $item = $global[0];

            if (!empty($active[0]) && $active[0]->getActive() == 1) {
                $item->setActive(1);
            } else {
                $item->setActive(0);
            }
        } else {
            $item = $result[0];

            if (!empty($active[0]) && $active[0]->getActive() == 1) {
                $item->setActive(1);
            } else {
                $item->setActive(0);
            }
        }

        $item->setIdDiba($library->getId());
        $item->setName($library->getName());
        $item->setMunicipality($library->getMunicipality());

        if ($library->getType() == 'biblioteca') {
            $item->setZone($library->getArea());
            $item->setType(1);
        } else {
            $area = $container
                ->get('doctrine')
                ->getRepository(Libraries::class)
                ->findBy(['municipality' => $library->getMunicipality()]);

            if (!is_null($area) && !empty($area) && !is_null($area[0]) && !empty($area[0])) {
                $item->setZone($area[0]->getZone());
            }

            $item->setType(2);
        }

        $item->setEmail($library->getEmail());
        $item->setCode($library->getGlobal());
        $item->setUpdatedAt(new \DateTime('now'));

        $entityManager->persist($item);
        $entityManager->flush();
    }
}
