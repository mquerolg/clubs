<?php

namespace App\Controller\Admin\Reports;

use App\Controller\Admin\Actions\ReportActions;
use App\Controller\Admin\AdminCrudController;
use App\Entity\Reports\LotsReport;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

/**
 * LotsReportCrudController
 */
class LotsReportCrudController extends AdminCrudController
{
    use ReportActions;
    
    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->setAdminAccess(true);
        $this->setUserAccess(false);
    }

    public static function getEntityFqcn(): string
    {
        return LotsReport::class;
    }

    /*
    |--------------------------------------------------------------------------
    | FIELDS
    |--------------------------------------------------------------------------
    */

    /**
     * Codi Library
     *
     * @return iterable
     */
    public function configureAdminIndexFields(): iterable
    {
        return [
            IntegerField::new('year'),
            IntegerField::new('created'),
            IntegerField::new('discharged'),
            IntegerField::new('borrowed'),
            IntegerField::new('total'),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('year')
            ->add('created')
            ->add('discharged')
            ->add('borrowed')
            ->add('total')
        ;
    }
}
