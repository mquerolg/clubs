<?php

namespace App\Controller\Admin;

use App\Diba\Admin\Filter\LibrariesFilter;
use App\Diba\Admin\Filter\MunicipalityFilter;
use App\Diba\Admin\Filter\ZoneFilter;
use App\Diba\Helpers\CronHelper;
use App\Entity\Historic;
use App\Entity\Libraries;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Controller\Admin\Actions\LibrariesActions;

/**
 * LibrariesCrudController
 */
class LibrariesCrudController extends AdminCrudController
{
    use LibrariesActions;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->setTemplateDetailName('crud/details/libraries');
        $this->setTemplateEditName('crud/edit/libraries');
        $this->setSortable(true);
        $this->setAdminAccess(true);
        $this->setUserAccess(false);
    }

    public static function getEntityFqcn(): string
    {
        return Libraries::class;
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
            ->andWhere('entity.closedAt IS NULL')
            ->andWhere('entity.libraryId = :libraryId ')
            ->setParameter('libraryId', $entityInstance->getId());

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
            TextField::new('code'),
            TextField::new('name', 'library_name_index'),
            TextField::new('municipality'),
            TextField::new('zone'),
            AssociationField::new('localization'),
            IntegerField::new('totalClubs', 'club_table'),
            IntegerField::new('useLots'),
            IntegerField::new('totalLots'),
        ];
    }

    public function configureAdminDetailFields(): iterable
    {
        return [
            BooleanField::new('active'),
            AssociationField::new('clubs', 'read_clubs'),
            TextField::new('code'),
            TextField::new('name', 'library_name'),
            TextField::new('municipality'),
            TextField::new('zone'),
            TextField::new('email'),
            TextField::new('observations'),
            AssociationField::new('localization'),
            IntegerField::new('useLots'),
            IntegerField::new('totalLots'),
        ];
    }

    public function configureAdminNewFields(): iterable
    {
        return [
            BooleanField::new('active'),
            TextField::new('name', 'library_name')->setFormTypeOption('disabled', 'disabled'),
            TextField::new('code')->setFormTypeOption('disabled', 'disabled'),
            TextField::new('localization')->setFormTypeOption('disabled', 'disabled'),
            TextField::new('municipality')->setFormTypeOption('disabled', 'disabled'),
            TextField::new('zone')->setFormTypeOption('disabled', 'disabled'),
            TextField::new('email')->setFormTypeOption('disabled', 'disabled'),
            IntegerField::new('useLots')->setFormTypeOption('disabled', 'disabled'),
            IntegerField::new('totalLots')->setFormTypeOption('disabled', 'disabled'),
            TextareaField::new('observations'),
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
        $qbl = $this->container->get('doctrine')->getManager()->createQueryBuilder();
        $qbz = $this->container->get('doctrine')->getManager()->createQueryBuilder();

        return $filters
            ->add(MunicipalityFilter::new($qbm, 'municipality'))
            ->add(LibrariesFilter::new($qbl, 'library'))
            ->add(ZoneFilter::new($qbz, 'zone'))
            //->add(EntityFilter::new('localization')->setFormTypeOption('value_type_options', ['multiple'=> true]));
        ;

        return $filters;
    }
}
