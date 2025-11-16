<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Actions\LotsDeletedActions;
use App\Diba\Admin\Filter\LangFilter;
use App\Diba\Admin\Filter\TextFilter;
use App\Entity\Deleted\LotsDeleted;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * LotsDeletedCrudController
 */
class LotsDeletedCrudController extends AdminCrudController
{
    use LotsDeletedActions;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->setTemplateDetailName('crud/details/lots');
        $this->setTemplateIndexName('crud/index/lots');
        $this->setAdminAccess(true);
        $this->setUserAccess(false);
        $this->setSortable(true);
        $this->setTitels([
            'index' => 'Lots',
        ]);
    }

    public static function getEntityFqcn(): string
    {
        return LotsDeleted::class;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    public function configCustomResponse(QueryBuilder $response): QueryBuilder
    {
        $response->andWhere('entity.deletedAt IS NOT NULL');

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | VIEWS
    |--------------------------------------------------------------------------
    */

    public function new(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)
                ->setController(LotsCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FIELDS
    |--------------------------------------------------------------------------
    */

    public function configureAdminIndexFields(): iterable
    {
        return [
            TextField::new('authorship', 'Authorship'),
            TextField::new('title', 'Title'),
            AssociationField::new('genre', 'Genre'),
            IntegerField::new('langCat', 'Lang Cat'),
            IntegerField::new('langEs', 'Lang Es'),
            IntegerField::new('langOthers', 'Lang Others'),
            TextField::new('signature', 'Signature'),
            TextField::new('warehouse', 'Warehouse'),
            TextField::new('owner', 'owner_table'),
            IntegerField::new('year', 'year_table'),
            TextField::new('deletedStatus', 'Status'),
        ];
    }

    public function configureAdminDetailFields(): iterable
    {
        return [
            TextField::new('bibliographic'),
            TextField::new('signature'),
            AssociationField::new('genre'),
            BooleanField::new('active'),
            TextField::new('authorship'),
            TextField::new('publication'),
            TextField::new('isbn'),
            TextField::new('description'),
            TextField::new('title'),
            TextField::new('collection'),
            TextField::new('classification'),
            UrlField::new('url'),
            IntegerField::new('langCat'),
            IntegerField::new('langEs'),
            IntegerField::new('langAng'),
            IntegerField::new('langFra'),
            IntegerField::new('langAle'),
            IntegerField::new('langIta'),
            TextField::new('warehouse', 'warehaouse_code'),
            TextField::new('dim'),
            IntegerField::new('uses'),
            TextField::new('owner'),
            IntegerField::new('year', 'entry_year'),
            TextField::new('observations'),
            AssociationField::new('copies'),
            AssociationField::new('status'),
            AssociationField::new('historic'),
            TextField::new('deletedStatus'),
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
            ->add(TextFilter::new('title'))
            ->add(TextFilter::new('authorship'))
            ->add(EntityFilter::new('genre')->setFormTypeOption('value_type_options', ['multiple' => true]))
            ->add(LangFilter::new('langs'))
            ->add(NumericFilter::new('langSum'))
            ->add(TextFilter::new('signature', 'sign'))
            ->add(TextFilter::new('owner', 'owner_filter'))
            ->add(TextFilter::new('warehouse'))
            ->add(NumericFilter::new('year'))
        ;
    }
}
