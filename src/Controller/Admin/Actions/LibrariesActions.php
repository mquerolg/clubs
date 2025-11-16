<?php

namespace App\Controller\Admin\Actions;

use App\Diba\Helpers\CronHelper;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * LibrariesActions
 */
trait LibrariesActions
{
    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('exportAction', 'excel_export')
            ->setIcon('fa fa-download')
            ->linkToCrudAction('exportAction')
            ->setCssClass('btn btn-secondary')
            ->createAsGlobalAction();

        $import = Action::new('importAction', 'update_date')
            ->setIcon('fa fa-refresh')
            ->linkToCrudAction('importAction')
            ->setCssClass('btn btn-primary')
            ->createAsGlobalAction();

        return $actions
            // ... Globals
            ->add(Crud::PAGE_INDEX, $import)
            ->add(Crud::PAGE_INDEX, $export)
            // ... InLine
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // ... Updates
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-file-text-o')->setCssClass('btn-line-action');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-pencil')->setCssClass('btn-line-action');
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-pencil')->setLabel('Editar')->setCssClass('btn btn--add-action');
            })
            // ... Disable
            ->disable('index', Action::NEW, Action::DELETE, Action::BATCH_DELETE, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            // ... Remove

            // ... Reorder
        ;
    }

    public function importAction(AdminContext $context)
    {
        CronHelper::updateLibraries($this->container);

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)
                ->setAction(Action::INDEX)
                ->unset(EA::ENTITY_ID)
                ->generateUrl()
        );
    }
}
