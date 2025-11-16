<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Actions\GenresActions;
use App\Entity\Genres;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * GenresCrudController
 */
class GenresCrudController extends AdminCrudController
{
    use GenresActions;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct()
    {
        $this->setAdminAccess(true);
        $this->setUserAccess(false);
        $this->setSortable(true);
        $this->setTemplateIndexName('crud/index/genres');
    }

    public static function getEntityFqcn(): string
    {
        return Genres::class;
    }

    /*
    |--------------------------------------------------------------------------
    | FIELDS
    |--------------------------------------------------------------------------
    */

    public function configureAdminIndexFields(): iterable
    {
        return [
            TextField::new('name', 'name_genre'),
            BooleanField::new('active'),
        ];
    }
}
