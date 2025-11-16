<?php

namespace App\Controller\Admin\Actions;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * ReportActions
 */
trait ReportActions
{
    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('exportAction', 'excel_export', 'fa fa-download')
            ->linkToCrudAction('exportAction')
            ->setCssClass('btn btn-secondary')
            ->createAsGlobalAction();

        return $actions
            // ... Globals
            ->add(Crud::PAGE_INDEX, $export)
            // ... InLine

            // ... Updates

            // ... Disable
            ->disable('index', Action::DELETE, Action::DETAIL, Action::NEW, Action::EDIT)
            // ... Remove

            // ... Reorder
        ;
    }
}
