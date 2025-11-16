<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Actions\IncidencesActions;
use App\Diba\Admin\Filter\AuthorshipFilter;
use App\Diba\Admin\Filter\TitleFilter;
use App\Entity\Cruds\Incidences;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

/**
 * IncidencesCrudController
 */
class IncidencesCrudController extends AdminCrudController
{
    use IncidencesActions;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->setAdminAccess(true);
        $this->setUserAccess(false);
        $this->setTemplateDetailName('crud/details/incidences');
        $this->setTitels([
            'detail' => 'incidence_detail',
        ]);
    }

    public static function getEntityFqcn(): string
    {
        return Incidences::class;
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
            ->andWhere('entity.incidence IS NOT NULL')
            ->andWhere('entity.returnedAt IS NOT NULL');

        return $response;
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
            AssociationField::new('library'),
            AssociationField::new('club', 'club_table'),
            AssociationField::new('authorship'),
            AssociationField::new('lot', 'Title'),
            AssociationField::new('warehouse'),
        ];
    }

    public function configureAdminDetailFields(): iterable
    {
        return [
            AssociationField::new('authorship'),
            AssociationField::new('lot', 'Title'),
            AssociationField::new('warehouse'),
            DateField::new('createdAt', 'incidence_petition'),
            DateField::new('returnedAt', 'incidence_return'),
            AssociationField::new('club', 'incidence_club'),
            AssociationField::new('library'),
            AssociationField::new('municipality'),
            TextareaField::new('incidence', 'incidence_text'),
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
            ->add(EntityFilter::new('library')->setFormTypeOption('value_type_options', ['multiple' => true]))
            ->add(EntityFilter::new('club', 'club_table')->setFormTypeOption('value_type_options', ['multiple' => true]))
            ->add(TitleFilter::new('lot', 'Title'))
            ->add(AuthorshipFilter::new('authorship'))
        ;
    }
}
