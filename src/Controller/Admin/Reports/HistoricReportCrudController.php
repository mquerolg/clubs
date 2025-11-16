<?php

namespace App\Controller\Admin\Reports;

use App\Controller\Admin\Actions\ReportActions;
use App\Controller\Admin\AdminCrudController;
use App\Diba\Admin\Filter\AuthorshipFilter;
use App\Diba\Admin\Filter\DateTimeFilter;
use App\Diba\Admin\Filter\GenreFilter;
use App\Diba\Admin\Filter\MunicipalityFilter;
use App\Diba\Admin\Filter\TitleFilter;
use App\Diba\Admin\Filter\WarehouseFilter;
use App\Entity\Reports\HistoricReport;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

/**
 * HistoricReportCrudController
 */
class HistoricReportCrudController extends AdminCrudController
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
        return HistoricReport::class;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    public function configCustomResponse(QueryBuilder $response): QueryBuilder
    {
        $response = $this->addJoin($response, [
            'entity.club' => 'club',
            'entity.lot' => 'lot',
            'entity.library' => 'library',
        ]);

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | SORTING
    |--------------------------------------------------------------------------
    */

    public function replaceJoinOrderBy(QueryBuilder $queryBuilder, string $alias, string $entityAlias, string $orderBy): QueryBuilder
    {
        $sortMap = [
            'authorship' => 'authorship.authorship',
            'lot' => 'lot.title',
            'library' => 'library.name',
            'club' => 'club.name',
            'municipality' => 'municipality.municipality',
            'warehouse' => 'warehouse.warehouse',
        ];

        $sort = $sortMap[$alias] ?? null;

        return !is_null($sort) ?
                $queryBuilder->orderBy($this->makeSort($sort), $orderBy)
            :
                $queryBuilder
        ;
    }

    /*
    |--------------------------------------------------------------------------
    | FIELDS
    |--------------------------------------------------------------------------
    */

    public function configureAdminIndexFields(): iterable
    {
        return [
            DateField::new('createdAt'),
            DateField::new('returnedAt'),
            AssociationField::new('municipality'),
            AssociationField::new('library'),
            AssociationField::new('club', 'club_table'),
            AssociationField::new('authorship'),
            AssociationField::new('lot', 'Title'),
            AssociationField::new('genre'),
            AssociationField::new('warehouse'),
            IntegerField::new('uses'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    public function configureFilters(Filters $filters): Filters
    {
        $qbm = $this->container->get('doctrine')->getManager()->createQueryBuilder();
        $qbg = $this->container->get('doctrine')->getManager()->createQueryBuilder();
        $qbw = $this->container->get('doctrine')->getManager()->createQueryBuilder();

        return $filters
                ->add(MunicipalityFilter::new($qbm, 'municipality'))
                ->add(EntityFilter::new('library')->setFormTypeOption('value_type_options', ['multiple' => true]))
                ->add(EntityFilter::new('club', 'club_table')->setFormTypeOption('value_type_options', ['multiple' => true]))
                ->add(TitleFilter::new('lot', 'Title'))
                ->add(AuthorshipFilter::new('authorship'))
                ->add(GenreFilter::new($qbg, 'genre'))
                ->add(WarehouseFilter::new($qbw, 'code'))
                ->add(DateTimeFilter::new('createdAt'))
                ->add(DateTimeFilter::new('returnedAt'))
                ->add('uses')
        ;
    }
}
