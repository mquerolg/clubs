<?php

namespace App\Controller;

use App\Entity\Clubs;
use App\Entity\Cruds\Incidences;
use App\Entity\Cruds\MyClubs;
use App\Entity\Cruds\UseLots;
use App\Entity\Genres;
use App\Entity\Historic;
use App\Entity\Libraries;
use App\Entity\Lots;
use App\Entity\Reports\HistoricReport;
use App\Entity\Shipments;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AbstractDashboardController
{
    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->showEntityActionsInlined(true)
            // the max number of entities to display per page
            ->setPaginatorPageSize($this->getPageSize())
            // the number of pages to display on each side of the current page
            // e.g. if num pages = 35, current page = 7 and you set ->setPaginatorRangeSize(4)
            // the paginator displays: [Previous]  1 ... 3  4  5  6  [7]  8  9  10  11 ... 35  [Next]
            // set this number to 0 to display a simple "< Previous | Next >" pager
            ->setPaginatorRangeSize(2)
            // these are advanced options related to Doctrine Pagination
            // (see https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/tutorials/pagination.html)
            ->setPaginatorUseOutputWalkers(true)
            ->setPaginatorFetchJoinCollection(true)
        ;
    }

    public function getPageSize()
    {
        $session = $this->container->get('session');
        $size = (int) Request::createFromGlobals()->get('pagesize');

        if (!is_null($size) && !empty($size)) {
            $session->set('pageSize', $size);
        }

        return $session->has('pageSize') ? $session->get('pageSize') : 10;
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Clubs de lectura')
            ->generateRelativeUrls();
    }

    public function configureMenuItems(): iterable
    {
        $session = $this->container->get('session');

        if (SecurityController::isAdmin($session)) {
            yield MenuItem::linkToCrud('menu_lots', null, Lots::class);
            yield MenuItem::linkToCrud('menu_uselots', null, UseLots::class);
            yield MenuItem::linkToCrud('menu_incidences', null, Incidences::class);
            yield MenuItem::linkToCrud('menu_historic', null, Historic::class);
            yield MenuItem::linkToCrud('menu_libraries', null, Libraries::class);
            yield MenuItem::linkToCrud('menu_clubs', null, Clubs::class);
            yield MenuItem::linkToCrud('menu_genres', null, Genres::class);
            yield MenuItem::linkToCrud('menu_trameses', null, Shipments::class);
            yield MenuItem::linkToCrud('menu_report', null, HistoricReport::class);
        } else {
            yield MenuItem::linkToCrud('menu_lots', null, Lots::class);
            yield MenuItem::linkToCrud('menu_mylots', null, UseLots::class);
            yield MenuItem::linkToCrud('menu_myclubs', null, MyClubs::class);
            yield MenuItem::linkToCrud('menu_historic', null, Historic::class);
            yield MenuItem::linkToCrud('menu_trameses', null, Shipments::class);
        }
    }
}
