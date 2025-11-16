<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Actions\UseLotsActions;
use App\Diba\Admin\Filter\AuthorshipFilter;
use App\Diba\Admin\Filter\GenreFilter;
use App\Diba\Admin\Filter\MunicipalityFilter;
use App\Diba\Admin\Filter\StatusFilter;
use App\Diba\Admin\Filter\TitleFilter;
use App\Diba\Admin\Filter\WarehouseFilter;
use App\Diba\Helpers\StateHelper as State;
use App\Entity\Cruds\UseLots;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * UseLotsCrudController
 */
class UseLotsCrudController extends AdminCrudController
{
    use UseLotsActions;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->setTemplateDetailName('crud/details/lots');
        $this->setTemplateIndexName('crud/index/uselots');
        $this->setSortable(true);
        $this->setAdminAccess(true);
        $this->setUserAccess(true);
    }

    public function configureCrud(Crud $crud): Crud
    {
        if ($this->isAdmin()) {
            return $crud->setPageTitle('index', 'use_lots');
        }

        return $crud->setPageTitle('index', 'mi_clubs');
    }

    public static function getEntityFqcn(): string
    {
        return UseLots::class;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    public function configCustomResponse(QueryBuilder $response): QueryBuilder
    {
        $response = $this->addJoin($response, [
            'entity.lot' => 'lot',
            'entity.library' => 'library',
            'entity.status' => 'status',
        ]);

        $response
            ->andWhere('entity.closedAt IS NULL')
            ->andWhere('lot.statusId > ' . State::RESERVED);

        if (!$this->isAdmin()) {
            $response
                ->andWhere('entity.libraryId = :userLibrary')
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
            'municipality' => 'municipality.municipality',
            'signature' => 'signature.signature',
            'warehouse' => 'warehouse.warehouse',
            'owner' => 'owner.owner',
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
            AssociationField::new('authorship', 'Authorship'),
            AssociationField::new('lot', 'Title'),
            AssociationField::new('genre', 'Genre'),
            AssociationField::new('library', 'Library'),
            AssociationField::new('municipality', 'Municipality'),
            TextField::new('route', 'Route'),
            AssociationField::new('langSum', 'Lang Sum'),
            AssociationField::new('signature', 'Signature'),
            AssociationField::new('warehouse', 'Warehouse'),
            AssociationField::new('owner', 'owner_table'),
            TextField::new('sendId', 'Send Id'),
            AssociationField::new('status', 'Status'),
        ];
    }

    public function configureUserIndexFields(): iterable
    {
        return [
            DateField::new('createdAt'),
            DateField::new('receivedIn'),
            DateField::new('receivedAt'),
            AssociationField::new('authorship'),
            AssociationField::new('lot', 'Title'),
            AssociationField::new('club', 'club_table'),
            TextField::new('sendId'),
            AssociationField::new('status'),
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
        $qbs = $this->container->get('doctrine')->getManager()->createQueryBuilder();
        $qbg = $this->container->get('doctrine')->getManager()->createQueryBuilder();
        $qbw = $this->container->get('doctrine')->getManager()->createQueryBuilder();

        return $this->isAdmin() ?
            $filters
                ->add(MunicipalityFilter::new($qbm, 'municipality'))
                ->add(EntityFilter::new('library')->setFormTypeOption('value_type_options', ['multiple' => true]))
                ->add(TitleFilter::new('lot', 'Title'))
                ->add(AuthorshipFilter::new('authorship'))
                ->add(GenreFilter::new($qbg, 'genre'))
                ->add(WarehouseFilter::new($qbw, 'code'))
                ->add(StatusFilter::new($qbs, 'status'))
        :
            $filters
        ;
    }

    /*
    |--------------------------------------------------------------------------
    | VIEWS
    |--------------------------------------------------------------------------
    */

    public function detail(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $id = $context->getEntity()->getInstance()->getLot()->getId();

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)
                ->setController(LotsCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($id)
                ->generateUrl()
        );
    }
}
