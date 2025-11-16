<?php

namespace App\Controller\Admin\Actions;

use App\Entity\Cruds\MyClubs;
use App\Entity\Historic;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

/**
 * MyClubsActions
 */
trait MyClubsActions
{
    /*
    |--------------------------------------------------------------------------
    | ACTIONS
    |--------------------------------------------------------------------------
    */

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('index', Action::BATCH_DELETE, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
        ;
    }

    public function index(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $entityInstance = $this->getUserLibrary();

        $queryBuilder = $this->container->get('doctrine')
            ->getManagerForClass($this->getEntityFqcn())->createQueryBuilder()
            ->select('entity')->from(MyClubs::class, 'entity')
            ->andWhere('entity.libraryId = :libraryId ')
            ->andWhere('entity.active = 1 ')
            ->setParameter('libraryId', $entityInstance->getId())
            ->orderBy('entity.name', 'DESC')
        ;

        $clubs = $queryBuilder->getQuery()->getResult();
        $dataClubs = [];

        if (is_countable($clubs) && count($clubs) > 0) {
            foreach ($clubs as $club) {
                $queryBuilder = $this->container->get('doctrine')
                    ->getManagerForClass($this->getEntityFqcn())->createQueryBuilder()
                    ->select('entity')->from(Historic::class, 'entity')
                    ->andWhere('entity.closedAt IS NOT NULL')
                    ->andWhere('entity.libraryId = :libraryId ')
                    ->andWhere('entity.clubId = :clubId ')
                    ->setParameter('libraryId', $entityInstance->getId())
                    ->setParameter('clubId', $club->getId())
                    ->orderBy('entity.id', 'DESC')
                    ->setMaxResults(25);

                $table = $queryBuilder->getQuery()->getResult();
                $dataHistory = [];

                foreach ($table as $tableData) {
                    $dataHistory[] = [
                        'createdAt' => $tableData->getCreatedAt(),
                        'returnedAt' => $tableData->getReturnedAt(),
                        'authorship' => $tableData->getLot()->getAuthorship(),
                        'title' => $tableData->getLot()->getTitle(),
                        'langCat' => $tableData->getLot()->getLangCat(),
                        'langEs' => $tableData->getLot()->getLangEs(),
                        'langSum' => $tableData->getLot()->getLangSum(),
                        'idClub' => $club->getId(),
                        'sendId' => $tableData->getSendId(),
                    ];
                }

                $dataClubs[] = [
                    'id' => $club->getId(),
                    'name' => $club->getName(),
                    'year' => $club->getyear(),
                    'external' => $club->getExternal(),
                    'library' => $club->getLibrary(),
                    'typology' => $club->getTypology(),
                    'description' => $club->getDescription(),
                    'active' => $club->getActive(),
                    'observations' => $club->getObservations(),
                    'dataHistory' => $dataHistory,
                ];
            }
        }

        $responseParameters = [
            'pageName' => Crud::PAGE_INDEX,
            'templateName' => $this->getTemplateIndexName(),
            'entity' => $context->getEntity(),
            'dataClubs' => $dataClubs,
        ];

        return $this->configureResponseParameters(
            KeyValueStore::new($this->configureIndexResponseParameters($context, $responseParameters))
        );
    }
}
