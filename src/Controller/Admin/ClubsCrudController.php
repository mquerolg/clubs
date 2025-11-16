<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Actions\ClubsActions;
use App\Diba\Admin\Filter\MunicipalityFilter;
use App\Diba\Admin\Filter\ZoneFilter;
use App\Entity\Clubs;
use App\Entity\Historic;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

use Symfony\Component\Security\Core\Security;

// use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
// use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
// use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
// use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;

/**
 * ClubsCrudController
 */
class ClubsCrudController extends AdminCrudController
{
    use ClubsActions;
    private $security;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->setTemplateDetailName('crud/details/clubs');
        $this->setTemplateNewName('crud/new/clubs');
        $this->setTemplateEditName('crud/edit/clubs');
        $this->setSortable(true);
        $this->setAdminAccess(true);
        $this->setUserAccess(false);
        $this->setTitels([
            'new' => 'new_club',
        ]);
    }

    public static function getEntityFqcn(): string
    {
        return Clubs::class;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    public function configCustomResponse(QueryBuilder $response): QueryBuilder
    {
        $response = $this->addJoin($response, [
            'entity.library' => 'library',
        ]);

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | INSTANCES
    |--------------------------------------------------------------------------
    */

    public function configureNewInstance($entityInstance)
    {
        $entityInstance->getLibraryMunicipality()->addClub();

        return $entityInstance;
    }

    public function configureDeleteInstance($entityInstance)
    {
        $entityInstance->getlibrary()->substractClub();

        return $entityInstance;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    public function configureDetailResponseParameters(AdminContext $context, array $responseParameters): array
    {
        $entityInstance = $context->getEntity()->getInstance();

        $queryBuilder = $this->container->get('doctrine')
            ->getManagerForClass($this->getEntityFqcn())->createQueryBuilder()
            ->select('entity')->from(Historic::class, 'entity')
            ->andWhere('entity.closedAt IS NOT NULL')
            ->andWhere('entity.clubId = :clubId ')
            ->setParameter('clubId', $entityInstance->getId())
            ->orderBy('entity.id', 'DESC')
            ->setMaxResults(25);

        $responseParameters['rows'] = $queryBuilder->getQuery()->getResult();

        return $responseParameters;
    }

    /*
    |--------------------------------------------------------------------------
    | SORTING
    |--------------------------------------------------------------------------
    */

    public function replaceJoinOrderBy(QueryBuilder $queryBuilder, string $alias, string $entityAlias, string $orderBy): QueryBuilder
    {
        $sortMap = [
            'localization' => 'localization.route',
            'library' => 'library.name',
            'municipality' => 'municipality.municipality',
            'zone' => 'zone.zone',
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
            TextField::new('name', 'table_clubs'),
            AssociationField::new('library'),
            AssociationField::new('municipality'),
            AssociationField::new('zone'),
            TextField::new('route'),
            IntegerField::new('useLots'),
            IntegerField::new('totalLots'),
        ];
    }

    public function configureAdminDetailFields(): iterable
    {
        return [
            TextField::new('name', 'name_library_club'),
            IntegerField::new('year', 'create_year'),
            BooleanField::new('external'),
            AssociationField::new('library'),
            TextField::new('typology'),
            TextareaField::new('description', 'club_description'),
            TextareaField::new('observations'),
            BooleanField::new('active'),
        ];
    }

    public function configureAdminNewFields(): iterable
    {
        return [
            TextField::new('name', 'name_library_club'),
            IntegerField::new('year', 'create_year'),
            AssociationField::new('libraryMunicipality')->setQueryBuilder(function ($queryBuilder) {
                return $queryBuilder->andWhere('entity.active = 1');
            }),
            TextField::new('typology'),
            TextareaField::new('description', 'club_description'),
            TextareaField::new('observations'),
            BooleanField::new('active'),
            BooleanField::new('external'),
        ];
    }

    public function configureAdminEditFields(): iterable
    {
        return [
            TextField::new('name', 'name_library_club'),
            IntegerField::new('year', 'create_year'),
            AssociationField::new('libraryMunicipality')->setQueryBuilder(function ($queryBuilder) {
                return $queryBuilder->andWhere('entity.active = 1');
            })->setFormTypeOption('disabled', 'disabled'),
            TextField::new('typology'),
            TextareaField::new('description', 'club_description'),
            TextareaField::new('observations'),
            BooleanField::new('active'),
            BooleanField::new('external'),
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
        $qbz = $this->container->get('doctrine')->getManager()->createQueryBuilder();

        return $filters
            ->add(MunicipalityFilter::new($qbm, 'municipality'))
            ->add(EntityFilter::new('library')->setFormTypeOption('value_type_options', ['multiple' => true]))
            ->add(ZoneFilter::new($qbz, 'zone'))
        ;
    }
}
