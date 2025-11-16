<?php

namespace App\Controller\Admin\Actions;

use App\Controller\Admin\LotsCrudController;
use App\Controller\RouteController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * LotsDeletedActions
 */
trait LotsDeletedActions
{
    public function configureActions(Actions $actions): Actions
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);

        $url = $routeBuilder
                    ->setDashboard(RouteController::class)
                    ->setController(LotsCrudController::class);

        $lots = Action::new('lotsAction', 'Mostrar lots donats de baixa', 'fa fa-check-square')
            ->linkToUrl($url)
            ->setCssClass('btn btn--discharged-action-active')
            ->createAsGlobalAction();

        $export = Action::new('exportAction', 'excel_export', 'fa fa-download')
            ->linkToCrudAction('exportAction')
            ->setCssClass('btn btn-secondary')
            ->createAsGlobalAction();

        return $actions
                // ... Globals
                ->add(Crud::PAGE_INDEX, $export)
                ->add(Crud::PAGE_INDEX, $lots)
                ->add(Crud::PAGE_INDEX, $this->getColumnsAction())
                // ... InLine
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                // ... Updates
                ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                    return $action->setIcon('fa fa-plus')->setLabel('Afegir lot')->setCssClass('btn btn--add-action');
                })
                ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                    return $action->setLabel('Tornar')->setCssClass('')->displayAsLink();
                })
                ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                    return $action->setIcon('fa fa-file-text-o')->setCssClass('btn-line-action');
                })
                // ... Disable
                ->disable('index', Action::DELETE, Action::EDIT)
                // ... Remove

                // ... Reorder
                ->reorder(Crud::PAGE_INDEX, ['lotsAction', 'exportAction', Action::NEW, 'columnsAction'])
        ;
    }
}
