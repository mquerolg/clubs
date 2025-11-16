<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Actions\HistoricActions;
use App\Diba\Admin\Filter\AuthorshipFilter;
use App\Diba\Admin\Filter\DateTimeFilter;
use App\Diba\Admin\Filter\GenreFilter;
use App\Diba\Admin\Filter\MunicipalityFilter;
use App\Diba\Admin\Filter\TitleFilter;
use App\Diba\Admin\Filter\WarehouseFilter;
use App\Entity\Historic;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

/**
 * HistoricCrudController
 */
class HistoricCrudController extends AdminCrudController
{
    use HistoricActions;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->setTemplateIndexName('crud/index/historic');
        $this->setAdminAccess(true);
        $this->setUserAccess(true);
        $this->setSortable(true);
        $this->setTitels([
            'index' => 'historic_index_title',
        ]);
    }

    public static function getEntityFqcn(): string
    {
        return Historic::class;
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

        $response
            ->andWhere('entity.closedAt IS NOT NULL');

        if (!$this->isAdmin()) {
            $response
                ->andWhere('library.id = :userLibrary')
                ->setParameter('userLibrary', $this->getUserLibrary()->getId());
        }

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
        ];
    }

    public function configureUserIndexFields(): iterable
    {
        return [
            DateField::new('createdAt'),
            DateField::new('returnedAt'),
            AssociationField::new('club', 'club_table'),
            AssociationField::new('authorship'),
            AssociationField::new('lot', 'Title'),
            AssociationField::new('genre'),
            AssociationField::new('warehouse'),
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

        return $this->isAdmin() ?
            $filters
                ->add(MunicipalityFilter::new($qbm, 'municipality'))
                ->add(EntityFilter::new('library')->setFormTypeOption('value_type_options', ['multiple' => true]))
                ->add(EntityFilter::new('club', 'club_table')->setFormTypeOption('value_type_options', ['multiple' => true]))
                ->add(TitleFilter::new('lot', 'Title'))
                ->add(AuthorshipFilter::new('authorship'))
                ->add(GenreFilter::new($qbg, 'genre'))
                ->add(WarehouseFilter::new($qbw, 'code'))
                ->add(DateTimeFilter::new('createdAt'))
                ->add(DateTimeFilter::new('returnedAt'))
        :
            $filters
                ->add(EntityFilter::new('club', 'club_table')->setFormTypeOption('value_type_options', ['multiple' => true]))
                ->add(TitleFilter::new('lot', 'Title'))
                ->add(AuthorshipFilter::new('authorship'))
                ->add(GenreFilter::new($qbg, 'genre'))
                ->add(DateTimeFilter::new('createdAt'))
                ->add(DateTimeFilter::new('returnedAt'))
        ;
    }
}
