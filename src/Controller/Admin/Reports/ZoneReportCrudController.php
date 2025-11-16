<?php

namespace App\Controller\Admin\Reports;

use App\Controller\Admin\Actions\ReportActions;
use App\Controller\Admin\AdminCrudController;
use App\Diba\Admin\Filter\TextFilter;
use App\Entity\Reports\ZoneReport;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * ZoneReportCrudController
 */
class ZoneReportCrudController extends AdminCrudController
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
        return ZoneReport::class;
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
            TextField::new('zone'),
            IntegerField::new('clubs'),
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
            ->add(TextFilter::new('zone'))
            ->add('clubs')
            ->add('lots')
        ;
    }
}
