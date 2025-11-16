<?php

namespace App\Controller\Admin\Reports;

use App\Controller\Admin\Actions\ReportActions;
use App\Controller\Admin\AdminCrudController;
use App\Diba\Admin\Filter\TextFilter;
use App\Entity\Reports\GenresReport;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * GenresReportCrudController
 */
class GenresReportCrudController extends AdminCrudController
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
        return GenresReport::class;
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
            TextField::new('genre'),
            TextField::new('club'),
            TextField::new('library'),
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
            ->add(TextFilter::new('genre'))
            ->add(TextFilter::new('club'))
            ->add(TextFilter::new('library'))
            ->add('total')
        ;
    }
}
