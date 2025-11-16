<?php

namespace App\Controller\Admin\Reports;

use App\Controller\Admin\Actions\ReportActions;
use App\Controller\Admin\AdminCrudController;
use App\Diba\Admin\Filter\TextFilter;
use App\Entity\Reports\ClubsLotsReport;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * ClubsLotsReportCrudController
 */
class ClubsLotsReportCrudController extends AdminCrudController
{
    use ReportActions;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->setSortable(true);
        $this->setAdminAccess(true);
        $this->setUserAccess(false);
    }

    public static function getEntityFqcn(): string
    {
        return ClubsLotsReport::class;
    }

    /*
    |--------------------------------------------------------------------------
    | FIELDS
    |--------------------------------------------------------------------------
    */

    public function configureAdminIndexFields(): iterable
    {
        return [
            IntegerField::new('year'),
            TextField::new('club'),
            TextField::new('library'),
            TextField::new('municipality'),
            TextField::new('zone'),
            IntegerField::new('lots'),
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
            ->add(TextFilter::new('club'))
            ->add(TextFilter::new('library'))
            ->add(TextFilter::new('municipality'))
            ->add(TextFilter::new('zone'))
            ->add('lots')
        ;
    }
}
