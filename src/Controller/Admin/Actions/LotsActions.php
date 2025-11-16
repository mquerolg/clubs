<?php

namespace App\Controller\Admin\Actions;

use App\Controller\Admin\LotsCrudController;
use App\Controller\Admin\LotsDeletedCrudController;
use App\Controller\Admin\UseLotsCrudController;
use App\Controller\RouteController;
use App\Diba\Helpers\OptionsHelper as Options;
use App\Diba\Helpers\StateHelper as State;
use App\Diba\SamcService;
use App\Entity\Deleted\ClubsDeleted;
use App\Entity\Deleted\LotsDeleted;
use App\Entity\Historic;
use App\Entity\Libraries;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

/**
 * LotsActions
 */
trait LotsActions
{
    public function configureActions(Actions $actions): Actions
    {
        $user_library = is_null($this->getUserLibrary()) ? 0 : $this->getUserLibrary()->getId();

        $url = $this->container->get(AdminUrlGenerator::class)
                    ->setDashboard(RouteController::class)
                    ->setController(LotsDeletedCrudController::class);

        $discharged = Action::new('dischargedAction', 'dischargedAction', 'fa fa-square-o')
            ->linkToUrl($url)
            ->setCssClass('btn btn--discharged-action')
            ->createAsGlobalAction();

        $export = Action::new('exportAction', 'excel_export', 'fa fa-download')
            ->linkToCrudAction('exportAction')
            ->setCssClass('btn btn-secondary')
            ->createAsGlobalAction();

        $returned = Action::new('returnedAction', 'returned_lot')
            ->linkToCrudAction('returnedAction')
            ->setCssClass('btn btn-secondary')
            ->displayIf(static function ($entity) {
                return ($entity->getStatusId() == State::IS_RETURN || $entity->getStatusId() == State::IN_LIBRARY)
                    && $entity->getActive() == 1;
            });

        $active = Action::new('activeAction', 'action-active', 'fa fa-unlock-alt')
            ->linkToCrudAction('activeAction')
            ->setCssClass('btn-line-action')
            ->displayIf(static function ($entity) {
                return $entity->getStatusId() == State::AVAILABLE
                    && $entity->getActive() == 1
                    && $entity->getReserved() != State::IS_RESERVED;
            });

        $disabled = Action::new('disabledAction', 'action-disable', 'fa fa-lock')
            ->linkToCrudAction('disableAction')
            ->setCssClass('btn-line-action')
            ->displayIf(static function ($entity) {
                return $entity->getStatusId() == State::RESERVED
                    && $entity->getActive() == 1
                    && $entity->getReserved() != State::IS_RESERVED;
            });

        $reserve = Action::new('reserveAction', 'action-active', 'fa fa-unlock-alt')
            ->linkToCrudAction('reserveAction')
            ->setCssClass('btn-line-action')
            ->displayIf(static function ($entity) {
                return $entity->getStatusId() > State::RESERVED
                    && $entity->getActive() == 1
                    && $entity->getReserved() != State::IS_RESERVED;
            });

        $unreserve = Action::new('unreserveAction', 'action-disable', 'fa fa-lock')
            ->linkToCrudAction('unreserveAction')
            ->setCssClass('btn-line-action')
            ->displayIf(static function ($entity) {
                return $entity->getStatusId() > State::RESERVED
                    && $entity->getActive() == 1
                    && $entity->getReserved() == State::IS_RESERVED;
            });

        $request = Action::new('requestAction', 'action-request', 'fa fa-shopping-cart')
            ->linkToCrudAction('requestAction')
            ->setHtmlAttributes(['data-library' => $user_library])
            ->setCssClass('btn-line-action action-request');

        $request_detail = Action::new('requestDetailAction', 'Demanar lot')
            ->linkToCrudAction('requestAction')
            ->setCssClass('btn btn-secondary action-request')
            ->setHtmlAttributes(['data-library' => $user_library]);

        $unsolicitable = Action::new('unRequestAction', '', 'fa fa-shopping-cart')
            ->linkToUrl('#')
            ->setCssClass('btn-line-action btn-line-disabled');

        $change = Action::new('changeAction', 'Traspassar lot')
            ->linkToCrudAction('changeAction')
            ->setHtmlAttributes(['data-library' => $user_library])
            ->setCssClass('btn btn-secondary action-change')
            ->displayIf(static function ($entity) {
                return $entity->getStatusId() > State::IN_TRANSIT
                    && $entity->getActive() == 1;
            });

        if ($this->isAdmin()) {
            $request->displayIf(static function ($entity) {
                return $entity->getStatusId() <= State::RESERVED
                    && $entity->getActive() == 1;
            });
            $request_detail->displayIf(static function ($entity) {
                return $entity->getStatusId() <= State::RESERVED
                    && $entity->getActive() == 1;
            });
            $unsolicitable->displayIf(static function ($entity) {
                return $entity->getStatusId() > State::RESERVED
                    || $entity->getActive() != 1;
            });
        } else {
            $request->displayIf(static function ($entity) {
                return $entity->getStatusId() == State::AVAILABLE
                    && $entity->getActive() == 1;
            });
            $request_detail->displayIf(static function ($entity) {
                return $entity->getStatusId() == State::AVAILABLE
                    && $entity->getActive() == 1;
            });
            $unsolicitable->displayIf(static function ($entity) {
                return $entity->getStatusId() != State::AVAILABLE
                    || $entity->getActive() != 1;
            });
        }

        return $this->isAdmin() ?
            $actions
                // ... Globals
                ->add(Crud::PAGE_INDEX, $this->getColumnsAction())
                ->add(Crud::PAGE_INDEX, $export)
                ->add(Crud::PAGE_INDEX, $discharged)
                ->add(Crud::PAGE_DETAIL, $change)
                ->add(Crud::PAGE_DETAIL, $request_detail)
                ->add(Crud::PAGE_DETAIL, $returned)
                ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
                // ... InLine
                ->add(Crud::PAGE_INDEX, $request)
                ->add(Crud::PAGE_INDEX, $unsolicitable)
                ->add(Crud::PAGE_INDEX, $active)
                ->add(Crud::PAGE_INDEX, $disabled)
                ->add(Crud::PAGE_INDEX, $reserve)
                ->add(Crud::PAGE_INDEX, $unreserve)
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ... Updates
                ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                    return $action->setIcon('fa fa-plus')
                        ->setLabel('add_lot')
                        ->setCssClass('btn btn--add-action');
                })
                ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                    return $action->setIcon('fa fa-file-text-o')
                        ->setCssClass('btn-line-action');
                })
                ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                    return $action->setIcon('')->setLabel('delete_lot')->displayIf(static function ($entity) {
                        return $entity->getStatusId() == State::AVAILABLE;
                    });
                })
                ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                    return $action->setIcon('fa fa-pencil')
                        ->setCssClass('btn-line-action');
                })
                ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                    return $action->setIcon('fa fa-pencil')
                        ->setLabel('Editar')
                        ->setCssClass('btn btn--add-action');
                })
                ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                    return $action->setLabel('Tornar')
                        ->setCssClass('')
                        ->displayAsLink();
                })
                // ... Disable
                ->disable('index', Action::BATCH_DELETE, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
                // ... Remove
                ->remove(Crud::PAGE_INDEX, Action::DELETE)
                // ... Reorder
                ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT])
                ->reorder(Crud::PAGE_INDEX, ['dischargedAction', 'exportAction', Action::NEW, 'columnsAction'])
                ->reorder(Crud::PAGE_DETAIL, [ACTION::DELETE, 'requestDetailAction', 'changeAction', 'returnedAction', Action::INDEX, Action::EDIT])
        :
            $actions
                // ... Globals
                ->add(Crud::PAGE_INDEX, $this->getColumnsAction())
                ->add(Crud::PAGE_INDEX, $export)
                ->add(Crud::PAGE_DETAIL, $request_detail)
                ->add(Crud::PAGE_DETAIL, $returned)
                // ... InLine
                ->add(Crud::PAGE_INDEX, $request)
                ->add(Crud::PAGE_INDEX, $unsolicitable)
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ... Updates
                ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                    return $action->setIcon('fa fa-file-text-o')->setCssClass('btn-line-action');
                })
                // ... Disable
                ->disable('index', Action::DELETE, Action::NEW, Action::EDIT)
                // ... Remove

                // ... Reorder
                ->reorder(Crud::PAGE_INDEX, ['exportAction', 'columnsAction'])
        ;
    }

    public function activeAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $entityManager = $this->container
            ->get('doctrine')
            ->getManagerForClass($this->getEntityFqcn());

        $lot = $context->getEntity()->getInstance();
        $lot->setStatusId(State::RESERVED);

        $entityManager->persist($lot);
        $entityManager->flush();

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function disableAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $entityManager = $this->container
            ->get('doctrine')
            ->getManagerForClass($this->getEntityFqcn());

        $lot = $context->getEntity()->getInstance();
        $lot->setStatusId(State::AVAILABLE);

        $entityManager->persist($lot);
        $entityManager->flush();

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function reserveAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $entityManager = $this->container
            ->get('doctrine')
            ->getManagerForClass($this->getEntityFqcn());

        $lot = $context->getEntity()->getInstance();
        $lot->setReserved(true);

        $entityManager->persist($lot);
        $entityManager->flush();

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function unreserveAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $entityManager = $this->container
            ->get('doctrine')
            ->getManagerForClass($this->getEntityFqcn());

        $lot = $context->getEntity()->getInstance();
        $lot->setReserved(false);

        $entityManager->persist($lot);
        $entityManager->flush();

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function returnedAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $id = $context->getEntity()->getInstance()->getId();

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)
                ->setController(UseLotsCrudController::class)
                ->setAction('returnAction')
                ->setEntityId($id)
                ->generateUrl()
        );
    }

    public function requestAction(AdminContext $context): Response
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $lots = $context->getEntity()->getInstance();
        $library = $this->isAdmin() ? $context->getRequest()->get('library') : $this->getUserLibrary();
        $club = $context->getRequest()->get('club');
        $received_in = \DateTime::createFromFormat('d/m/Y', $context->getRequest()->get('senddate'));
        $return_in = \DateTime::createFromFormat('d/m/Y', $context->getRequest()->get('senddate'));
        $send_id = $context->getRequest()->get('sendid');

        if (!is_null($library) && !empty($library) && !is_null($club) && !empty($club) && !is_null($received_in) && !empty($received_in) && $received_in !== false && !is_null($send_id) && !empty($send_id) && $received_in !== false) {
            $library = $this->container->get('doctrine')->getRepository(Libraries::class)->find($library);
            $club = $this->container->get('doctrine')->getRepository(ClubsDeleted::class)->find($club);
            $lot = $this->container->get('doctrine')->getRepository(LotsDeleted::class)->find($lots->getId());

            if (!empty($library) && !empty($club)) {
                $open_historic = $lot->getHistoric()->filter(function ($entry) {
                    return is_null($entry->getClosedAt());
                })->count();

                if ($open_historic == 0) {
                    $entityManager = $this->container->get('doctrine')->getManagerForClass(Historic::class);

                    $lot->setUses($lot->getUses() + 1);
                    $lot->setStatusId(State::REQUESTED);
                    $library->addLot();
                    $club->addLot();

                    $historic = new Historic();
                    $historic->setLibrary($library);
                    $historic->setLot($lot);
                    $historic->setClub($club);
                    $historic->setUses($lot->getUses());
                    $historic->setRoute($library->getLocalization()->__toString());
                    $historic->setReceivedIn($received_in);
                    $historic->makeReturnInDate($return_in);
                    $historic->setSendId($send_id);

                    $urlGenerate = $this->container->get(AdminUrlGenerator::class)
                        ->setController(LotsCrudController::class)
                        ->setAction(Action::INDEX)
                        ->generateUrl();

                    $urlLink = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $urlGenerate;

                    $entityManager->persist($historic);
                    $entityManager->flush();

                    SamcService::notifyLotPetition(
                        $lot->getTitle(),
                        $historic->getSendId(),
                        $historic->getCreatedAt(),
                        $urlLink,
                        $library->getEmail(),
                        $historic->getLot()->getAuthorship(),
                        $lot->getWarehouse(),
                        $historic->getLibrary()->getCode(), 
                        $historic->getReceivedIn()
                    );
                }
            }
        }

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function changeAction(AdminContext $context): Response
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $lots = $context->getEntity()->getInstance();
        $library = $this->isAdmin() ? $context->getRequest()->get('library') : $this->getUserLibrary();
        $club = $context->getRequest()->get('club');

        if (!is_null($lots) && !empty($lots) && !is_null($library) && !empty($library) && !is_null($club) && !empty($club)) {
            $library = $this->container->get('doctrine')->getRepository(Libraries::class)->find($library);
            $club = $this->container->get('doctrine')->getRepository(ClubsDeleted::class)->find($club);
            $lot = $this->container->get('doctrine')->getRepository(LotsDeleted::class)->find($lots->getId());

            if (!empty($library) && !empty($club) && !empty($lot)) {
                $open_historic = $lot->getHistoric()->filter(function ($entry) {
                    return is_null($entry->getClosedAt());
                });

                if ($open_historic->count() == 1) {
                    $entityManager = $this->container->get('doctrine')->getManagerForClass(Historic::class);
                    $closedHistopric = $open_historic->first();
                    $sendId = $closedHistopric->getSendId();

                    // Calculate dates
                    $interval = Options::get('max_return_library') ?? 90;
                    $startDate = (new \DateTime())->modify('-1 day');
                    $returnDate = (new \DateTime())->modify($interval . ' day');

                    // Process return lot
                    $closedHistopric->setReturnedAt($startDate);
                    $closedHistopric->setPickedAt($startDate);
                    $closedHistopric->setClosedAt($startDate);
                    $closedHistopric->getClub()->substractLot();
                    $closedHistopric->getLibrary()->substractLot();

                    $entityManager->persist($closedHistopric);
                    $entityManager->flush();

                    // Process request lot
                    $lot->setUses($lot->getUses() + 1);
                    $lot->setStatusId(State::IN_LIBRARY);
                    $library->addLot();
                    $club->addLot();

                    $historic = new Historic();
                    $historic->setLibrary($library);
                    $historic->setLot($lot);
                    $historic->setClub($club);
                    $historic->setUses($lot->getUses());
                    $historic->setRoute($library->getLocalization()->__toString());
                    $historic->makeReturnInDate($returnDate);
                    $historic->setSendId($sendId);
                    $historic->setReceivedIn($startDate);
                    $historic->setTransitIn($startDate);
                    $historic->setReceivedAt($startDate);
                    $historic->setCreatedAt($startDate);

                    $entityManager->persist($historic);
                    $entityManager->flush();
                }
            }
        }

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::DETAIL)
                ->generateUrl()
        );
    }
}
