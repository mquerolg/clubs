<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Actions\ShipmentsActions;
use App\Entity\Shipments;

class ShipmentsCrudController extends AdminCrudController
{
    use ShipmentsActions;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public static function getEntityFqcn(): string
    {
        return Shipments::class;
    }

    public function __construct()
    {
        $this->setTemplateIndexName('crud/index/shipments');
        $this->setAdminAccess(true);
        $this->setUserAccess(true);
    }
}
