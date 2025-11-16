<?php

namespace App\Controller\Admin\Actions;

use App\Entity\Deleted\HistoricDeleted;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * HistoricActions
 */
trait HistoricActions
{
    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('exportAction', 'excel_export', 'fa fa-download')
            ->linkToCrudAction('exportAction')
            ->setCssClass('btn btn-secondary')
            ->createAsGlobalAction();

        $delete = Action::new('hidenAction', 'Eliminar', 'fa fa-trash')
            ->linkToCrudAction('hidenAction')
            ->setCssClass('btn-line-action modal-request-historic-hide');

        return $actions
            // ... Globals
            ->add(Crud::PAGE_INDEX, $export)
            // ... InLine
            ->add(Crud::PAGE_INDEX, $delete)
            // ... Updates

            // ... Disable
            ->disable('index', Action::NEW, Action::DELETE, Action::EDIT)
            // ... Remove

            // ... Reorder
        ;
    }

    public function hidenAction(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $entityManager = $this->container->get('doctrine')->getManagerForClass($this->getEntityFqcn());

        $id = $context->getEntity()->getInstance()->getId();

        $historic = $this->container->get('doctrine')->getRepository(HistoricDeleted::class)->find($id);
        $historic->setDeletedAt(new \DateTime('now'));

        $entityManager->persist($historic);
        $entityManager->flush();

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }
}
