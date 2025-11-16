<?php

namespace App\Controller\Admin\Actions;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * ClubsActions
 */
trait ClubsActions
{
    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('exportAction', 'excel_export')
            ->setIcon('fa fa-download')
            ->linkToCrudAction('exportAction')
            ->setCssClass('btn btn-secondary')
            ->createAsGlobalAction();

        return $actions
            // ... Globals
            ->add(Crud::PAGE_INDEX, $export)
            // ... InLine
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            // ... Updates
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->setIcon('')->setLabel('delete_club')->displayIf(static function ($entity) {
                    return $entity->getUseLots() == 0;
                });
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('add_club')->setCssClass('btn btn--add-action');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-file-text-o')->setCssClass('btn-line-action');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-pencil')->setCssClass('btn-line-action');
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-pencil')->setLabel('edit')->setCssClass('btn btn--add-action');
            })
            // ... Disable
            ->disable('index', Action::BATCH_DELETE, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            // ... Remove
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            // ... Reorder
        ;
    }
}
