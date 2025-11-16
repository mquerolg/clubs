<?php

namespace App\Controller\Admin\Actions;

use App\Diba\Helpers\OptionsHelper;
use App\Diba\Helpers\StateHelper as State;
use App\Diba\SamcService;
use App\Entity\Deleted\HistoricDeleted;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * UseLotsActions
 */
trait UseLotsActions
{
    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('exportAction', 'excel_export', 'fa fa-download')
            ->linkToCrudAction('exportAction')
            ->setCssClass('btn btn-secondary')
            ->createAsGlobalAction();

        $prepared = Action::new('preparedAction', 'action-prepared', 'fa fa-cube')
            ->linkToCrudAction('preparedAction')
            ->setCssClass('btn-line-action modal-request-uselots-prepared')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() < State::PREPARED;
            });
            
        $sended = Action::new('sendedAction', 'action-sended', 'fa fa-truck')
            ->linkToCrudAction('sendedAction')
            ->setCssClass('btn-line-action modal-request-uselots-sended')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() == State::PREPARED;
            });

        $unsended = Action::new('unsendedAction', '', 'fa fa-truck')
            ->linkToUrl('#')
            ->setCssClass('btn-line-action btn-line-disabled')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() >= State::IN_TRANSIT;
            });

        $received = Action::new('receivedAction', 'action-received', 'fa fa-check')
            ->linkToCrudAction('receivedAction')
            ->setCssClass('btn-line-action modal-request-uselots-received')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() <= State::IN_TRANSIT;
            });

        $unreceived = Action::new('unreceivedAction', '', 'fa fa-check')
            ->linkToUrl('#')
            ->setCssClass('btn-line-action btn-line-disabled')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() > State::IN_TRANSIT;
            });

        $renew = Action::new('renewAction', 'action-renew', 'fa fa-refresh')
            ->linkToCrudAction('renewAction')
            ->setCssClass('btn-line-action modal-request-uselots-renew')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() == State::IN_LIBRARY
                    || $entity->getLot()->getStatusId() == State::IS_RETURN;
            });

        $unrenew = Action::new('unrenewAction', '', 'fa fa-refresh')
            ->linkToUrl('#')
            ->setCssClass('btn-line-action btn-line-disabled')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() != State::IN_LIBRARY
                    && $entity->getLot()->getStatusId() != State::IS_RETURN;
            });

        $return = Action::new('returnAction', 'action-return', 'fa fa-calendar-plus-o')
            ->linkToCrudAction('returnAction')
            ->setCssClass('btn-line-action modal-request-uselots-return')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() == State::IN_LIBRARY
                    || $entity->getLot()->getStatusId() == State::IS_RETURN;
            });

        $unreturn = Action::new('unreturnAction', '', 'fa fa-calendar-plus-o')
            ->linkToUrl('#')
            ->setCssClass('btn-line-action btn-line-disabled')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() != State::IN_LIBRARY
                    && $entity->getLot()->getStatusId() != State::IS_RETURN;
            });

        $picked_up = Action::new('pickedUpAction', 'action-picked', 'fa fa-truck')
            ->linkToCrudAction('pickedUpAction')
            ->setCssClass('btn-line-action modal-request-uselots-picked')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() == State::IS_COLLECTED;
            });

        $unpicked_up = Action::new('unpickedUpAction', '', 'fa fa-truck')
            ->linkToUrl('#')
            ->setCssClass('btn-line-action btn-line-disabled')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() != State::IS_COLLECTED;
            });

        $returned = Action::new('returnedAction', 'action-returned', 'fa fa-building')
            ->linkToCrudAction('returnedAction')
            ->setCssClass('btn-line-action modal-request-uselots-returned')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() == State::IS_RETURNED;
            });

        $unreturned = Action::new('unreturnedAction', '', 'fa fa-building')
            ->linkToUrl('#')
            ->setCssClass('btn-line-action btn-line-disabled')
            ->displayIf(static function ($entity) {
                return $entity->getLot()->getStatusId() != State::IS_RETURNED;
            });

        $todayTenPm = new \DateTime('today 22:00');
        $todayMidnight = new \DateTime('today 00:00');

        $undo = Action::new('undoAction', 'action-undo', 'fa fa-undo')
            ->linkToCrudAction('undoAction')
            ->setCssClass('btn-line-action modal-request-uselots-undo')
            ->displayIf(static function ($entity) use ($todayTenPm, $todayMidnight) {
                return ($entity->getCreatedAt() >= $todayMidnight
                    && $entity->getCreatedAt() < $todayTenPm)
                    || ($entity->getLot()->getStatusId() >= State::PREPARED
                    && $entity->getLot()->getStatusId() < State::FINISHED);
            });

        $unundo = Action::new('unundoAction', '', 'fa fa-undo')
            ->linkToUrl('#')
            ->setCssClass('btn-line-action btn-line-disabled')
            ->displayIf(static function ($entity) use ($todayTenPm, $todayMidnight) {
                return !($entity->getCreatedAt() >= $todayMidnight
                    && $entity->getCreatedAt() < $todayTenPm)
                    && ($entity->getLot()->getStatusId() < State::PREPARED
                    || $entity->getLot()->getStatusId() > State::IS_RETURNED);
            });


        return $this->isAdmin() ?
            $actions
                // ... Globals
                ->add(Crud::PAGE_INDEX, $this->getColumnsAction())
                ->add(Crud::PAGE_INDEX, $export)
                // ... InLine
                ->add(Crud::PAGE_INDEX, $undo)
                ->add(Crud::PAGE_INDEX, $unundo)
                ->add(Crud::PAGE_INDEX, $unreturned)
                ->add(Crud::PAGE_INDEX, $returned)
                ->add(Crud::PAGE_INDEX, $unpicked_up)
                ->add(Crud::PAGE_INDEX, $picked_up)
                ->add(Crud::PAGE_INDEX, $unreturn)
                ->add(Crud::PAGE_INDEX, $return)
                ->add(Crud::PAGE_INDEX, $unrenew)
                ->add(Crud::PAGE_INDEX, $renew)
                ->add(Crud::PAGE_INDEX, $unreceived)
                ->add(Crud::PAGE_INDEX, $received)
                ->add(Crud::PAGE_INDEX, $unsended)
                ->add(Crud::PAGE_INDEX, $sended)
                ->add(Crud::PAGE_INDEX, $prepared)
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ... Updates
                ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                    return $action->setIcon('fa fa-file-text-o')->setCssClass('btn-line-action');
                })
                // ... Disable
                ->disable('index', Action::NEW, Action::DELETE, Action::EDIT)

                // ... Remove

                // ... Reorder
                ->reorder(Crud::PAGE_INDEX, ['exportAction', 'columnsAction'])
        :
            $actions
                // ... Globals
                ->add(Crud::PAGE_INDEX, $this->getColumnsAction())
                // ... InLine
                ->add(Crud::PAGE_INDEX, $unpicked_up)
                ->add(Crud::PAGE_INDEX, $picked_up)
                ->add(Crud::PAGE_INDEX, $unreturn)
                ->add(Crud::PAGE_INDEX, $return)
                ->add(Crud::PAGE_INDEX, $unreceived)
                ->add(Crud::PAGE_INDEX, $received)
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ... Updates
                ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                    return $action->setIcon('fa fa-file-text-o')->setCssClass('btn-line-action');
                })
                // ... Disable
                ->disable('index', Action::NEW, Action::DELETE, Action::EDIT)

                // ... Remove

                // ... Reorder
        ;
    }

    public function preparedAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $historic = $context->getEntity()->getInstance();

        if ($historic->getLot()->getStatusId() < State::PREPARED) {
            $entityHistoricManager = $this->container
                ->get('doctrine')
                ->getManagerForClass($this->getEntityFqcn());

            $historic->getLot()->setStatusId(State::PREPARED);
            $historic->setPreparetAt(new \DateTime('now'));
            $historic->setUpdatedAt(new \DateTime('now'));

            $entityHistoricManager->persist($historic);
            $entityHistoricManager->flush();
        }

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function sendedAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $historic = $context->getEntity()->getInstance();

        if ($historic->getLot()->getStatusId() < State::IN_TRANSIT) {
            $entityHistoricManager = $this->container
                ->get('doctrine')
                ->getManagerForClass($this->getEntityFqcn());

            // Sended but not prepared
            if (is_null($historic->getPreparetAt())) {
                $historic->setPreparetAt(new \DateTime('now'));
            }

            $historic->getLot()->setStatusId(State::IN_TRANSIT);
            $historic->setTransitIn(new \DateTime('now'));
            $historic->setUpdatedAt(new \DateTime('now'));

            $entityHistoricManager->persist($historic);
            $entityHistoricManager->flush();
        }

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function receivedAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $historic = $context->getEntity()->getInstance();

        if ($historic->getLot()->getStatusId() < State::IN_LIBRARY) {
            $entityHistoricManager = $this->container
                ->get('doctrine')
                ->getManagerForClass($this->getEntityFqcn());

            // Received but not prepared
            if (is_null($historic->getPreparetAt())) {
                $historic->setPreparetAt(new \DateTime('now'));
            }
        
            // Received but not sent
            if (is_null($historic->getTransitIn())) {
                $historic->setTransitIn(new \DateTime('now'));
            }

            $historic->getLot()->setStatusId(State::IN_LIBRARY);
            $historic->setReceivedAt(new \DateTime('now'));
            $historic->setUpdatedAt(new \DateTime('now'));

            $entityHistoricManager->persist($historic);
            $entityHistoricManager->flush();


            if ($this->isAdmin()) {
                SamcService::notifyAdminLotInTracking(
                    $historic->getLot()->getTitle(),
                    $historic->getLot()->getAuthorship(),
                    $historic->getLot()->getWarehouse(),
                    $historic->getLibrary()->getEmail()
                );
            } else {
                SamcService::notifyLotInTracking(
                    $historic->getLot()->getTitle(),
                    $historic->getCreatedAt(),
                    $historic->getLibrary()->getEmail(),
                    $historic->getLot()->getAuthorship(),
                    $historic->getLibrary()->getCode(),
                    $historic->getLot()->getWarehouse()
                );
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

    public function renewAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $historic = $context->getEntity()->getInstance();

        if (
            $historic->getLot()->getStatusId() == State::IN_LIBRARY
            || $historic->getLot()->getStatusId() == State::IS_RETURN
        ) {
            $entityHistoricManager = $this->container
                ->get('doctrine')
                ->getManagerForClass($this->getEntityFqcn());

            $historic->getLot()->setStatusId(State::IN_LIBRARY);

            $new_date = new \DateTime('now');
            $maxReturn = (mb_substr($historic->getSignature()->getSignature(), 0, 2) == 'LF')
                ? 'max_return_library_lf'
                : (($historic->getLibrary()->getType() == State::LIBRARY_TYPE)
                    ? 'max_return_library'
                    : 'max_return_bus');

            $new_date->add(new \DateInterval('P' . OptionsHelper::get($maxReturn) . 'D'));

            $historic->setReturnIn($new_date);
            $historic->setUpdatedAt(new \DateTime('now'));

            $entityHistoricManager->persist($historic);
            $entityHistoricManager->flush();
        }

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function returnAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $historic = $context->getEntity()->getInstance();
        $comment = $context->getRequest()->get('comment');

        if (
            $historic->getLot()->getStatusId() == State::IN_LIBRARY
            || $historic->getLot()->getStatusId() == State::IS_RETURN
        ) {
            $entityHistoricManager = $this->container
                ->get('doctrine')
                ->getManagerForClass($this->getEntityFqcn());

            $historic->getLot()->setStatusId(State::IS_COLLECTED);

            $historic->setReturnedAt(new \DateTime('now'));
            $historic->setUpdatedAt(new \DateTime('now'));

            if (isset($comment) && !empty($comment)) {
                $historic->setIncidence($comment);
            }

            $entityHistoricManager->persist($historic);
            $entityHistoricManager->flush();
        }

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function pickedUpAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $historic = $context->getEntity()->getInstance();

        if ($historic->getLot()->getStatusId() == State::IS_COLLECTED) {
            $entityHistoricManager = $this->container
                ->get('doctrine')
                ->getManagerForClass($this->getEntityFqcn());

            $historic->getLot()->setStatusId(State::IS_RETURNED);

            $historic->setPickedAt(new \DateTime('now'));
            $historic->setUpdatedAt(new \DateTime('now'));

            $entityHistoricManager->persist($historic);
            $entityHistoricManager->flush();
        }

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

        $historic = $context->getEntity()->getInstance();

        if ($historic->getLot()->getStatusId() == State::IS_RETURNED) {
            $entityHistoricManager = $this->container
                ->get('doctrine')
                ->getManagerForClass($this->getEntityFqcn());

            if ($historic->getLot()->getReserved()) {
                $historic->getLot()->setStatusId(State::RESERVED);
                $historic->getLot()->setReserved(!State::IS_RESERVED);
            } else {
                $historic->getLot()->setStatusId(State::AVAILABLE);
            }

            $historic->getClub()->substractLot();
            $historic->getLibrary()->substractLot();
            $historic->setClosedAt(new \DateTime('now'));
            $historic->setUpdatedAt(new \DateTime('now'));

            $entityHistoricManager->persist($historic);
            $entityHistoricManager->flush();
        }

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    public function undoAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $historic = $context->getEntity()->getInstance();

        $entityHistoricManager = $this->container
            ->get('doctrine')
            ->getManagerForClass($this->getEntityFqcn());

        if ($historic->getLot()->getStatusId() == State::IS_RETURNED) {
            $historic->getLot()->setStatusId(State::IS_COLLECTED);
            $historic->setPickedAt(null);
        } elseif (
            $historic->getLot()->getStatusId() == State::IS_COLLECTED
            || $historic->getLot()->getStatusId() == State::IS_RETURN
        ) {
            if ($historic->getReturnIn() > new \DateTime('now')) {
                $historic->getLot()->setStatusId(State::IN_LIBRARY);
            } else {
                $historic->getLot()->setStatusId(State::IS_RETURN);
            }

            $historic->setReturnedAt(null);
        } elseif ($historic->getLot()->getStatusId() == State::IN_LIBRARY) {
            $historic->getLot()->setStatusId(State::IN_TRANSIT);
            $historic->setReceivedAt(null);
        } elseif ($historic->getLot()->getStatusId() == State::IN_TRANSIT) {
            $historic->getLot()->setStatusId(State::PREPARED);
            $historic->setTransitIn(null);
        } elseif ($historic->getLot()->getStatusId() == State::PREPARED) {
            $historic->getLot()->setStatusId(State::REQUESTED);
            $historic->setTransitIn(null);
        } elseif ($historic->getLot()->getStatusId() == State::REQUESTED) {
            $todayTenPm = new \DateTime('today 22:00');
            $todayMidnight = new \DateTime('today 00:00');

            if ($historic->getCreatedAt() >= $todayMidnight && $historic->getCreatedAt() < $todayTenPm) {
                return $this->deleteHistoric($historic, $entityHistoricManager);
            }
        }

        $historic->setUpdatedAt(new \DateTime('now'));

        $entityHistoricManager->persist($historic);
        $entityHistoricManager->flush();

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }

    protected function deleteHistoric($historic, $entityHistoricManager)
    {
        $deleteHistoric = $this->container->get('doctrine')->getRepository(HistoricDeleted::class)->find($historic);
        $lot = $historic->getLot();
        $library = $historic->getLibrary();
        $club = $historic->getClub();

        $lot->setUses(max($lot->getUses() - 1, 0));
        $lot->setStatusId(State::AVAILABLE);
        $library->setUseLots(max($library->getUseLots() - 1, 0));
        $library->setTotalLots(max($library->getTotalLots() - 1, 0));
        $club->setUseLots(max($club->getUseLots() - 1, 0));
        $club->setTotalLots(max($club->getTotalLots() - 1, 0));

        $entityHistoricManager->persist($lot);
        $entityHistoricManager->persist($library);
        $entityHistoricManager->persist($club);
        $entityHistoricManager->remove($deleteHistoric);
        $entityHistoricManager->flush();

        return $this->redirect(
            $this->container
                ->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }
}
