<?php

namespace App\Controller\Admin\Actions;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * IncidencesActions
 */
trait IncidencesActions
{
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // ... Globals

            // ... InLine
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
}
