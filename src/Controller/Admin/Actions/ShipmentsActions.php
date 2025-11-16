<?php

namespace App\Controller\Admin\Actions;

use App\Diba\Helpers\OptionsHelper;
use App\Entity\Localizations;
use App\Entity\Shipments;
use App\Entity\ShipmentsMiddle;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

/**
 * ShipmentsActions
 */
trait ShipmentsActions
{
    public function configureActions(Actions $actions): Actions
    {
        $actionSelectRoute = Action::new('selectRoute')
            ->linkToCrudAction('selectRoute')
            ->createAsGlobalAction();

        return $actions
            // ... Globals
            ->add(Crud::PAGE_INDEX, $actionSelectRoute)
            // ... InLine
            // ... Updates
            // ... Disable
            ->disable(Action::DELETE, Action::DETAIL, Action::NEW, Action::EDIT)
            // ... Reorder
        ;
    }

    public function selectRoute(AdminContext $context)
    {
        $rutaSelected = $context->getRequest()->get('ruta');
        if (!empty($rutaSelected)) {
            $dataCalendar = $this->getShipmentsByRuta($rutaSelected);
        }

        $entityInstance = $this->container->get('doctrine')->getRepository(ShipmentsMiddle::class);
        $rutaData = $entityInstance->findAll(['tramDesc' => 'ASC']);
        $sizeofRoutes = is_countable($rutaData) ? count($rutaData) : 0;
        $ruta = [];

        for ($i = 0; $i < $sizeofRoutes; $i++) {
            $ruta[] = $rutaData[$i]->getTramDesc();
        }

        return $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_INDEX,
            'templateName' => $this->getTemplateIndexName(),
            'dataCalendar' => $dataCalendar,
            'selected' => $rutaSelected,
            'routesArray' => $ruta,
            'isAdmin' => true,
        ]));
    }

    public function index(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        if ($this->isAdmin()) {
            $entityInstance = $this->container->get('doctrine')->getRepository(ShipmentsMiddle::class);
            $rutaData = $entityInstance->findAll(['tramDesc' => 'ASC']);
            $sizeofRoutes = is_countable($rutaData) ? count($rutaData) : 0;
            $ruta = [];

            for ($i = 0; $i < $sizeofRoutes; $i++) {
                $ruta[] = $rutaData[$i]->getTramDesc();
            }

            return $this->configureResponseParameters(
                KeyValueStore::new([
                    'pageName' => Crud::PAGE_INDEX,
                    'templateName' => $this->getTemplateIndexName(),
                    'dataCalendar' => $this->getShipmentsByRuta($ruta[0]),
                    'selected' => $ruta[0],
                    'routesArray' => $ruta,
                    'isAdmin' => true,
                ])
            );
        }

        return $this->configureResponseParameters(
            KeyValueStore::new([
                'pageName' => Crud::PAGE_INDEX,
                'templateName' => $this->getTemplateIndexName(),
                'dataCalendar' => $this->getByBibliotecaId(),
                'isAdmin' => false,
            ])
        );
    }

    public function getShipmentsByRuta($ruta)
    {
        $entityInstance = $this->container->get('doctrine')->getRepository(Shipments::class);
        $data = $entityInstance->findBy(['route' => $ruta], ['lot' => 'DESC']);

        $sizeofDataOriginal = is_countable($data) ? count($data) : 0;

        $result = [];
        $data = array_merge($data, $data);

        $sizeofData = is_countable($data) ? count($data) : 0;
        $currrentYear = date('Y');

        if ($sizeofData > 0) {
            $months = ['Gener', 'Febrer', 'Març', 'Abril', 'Maig', 'Juny', 'Juliol', 'Agost', 'Setembre', 'Octubre', 'Novembre', 'Desembre'];
            $maxEntryDate = OptionsHelper::get('max_entry_date');

            for ($i = 0; $i < $sizeofData; $i++) {
                if ($sizeofDataOriginal >= ($i + 1) && $data[$i]->getStartDate() && $data[$i]->getEndDate() && explode('/', $data[$i]->getStartDate())[2] == $currrentYear) {
                    $dt = new \DateTime(intval(explode('/', $data[$i]->getStartDate())[2]) . '-' .
                                        intval(explode('/', $data[$i]->getStartDate())[1]) . '-' .
                                        intval(explode('/', $data[$i]->getStartDate())[0]));
                    $dt = $dt->modify('-' . $maxEntryDate . ' days');

                    $result[$i]['startDay'] = intval(explode('/', $data[$i]->getStartDate())[0]);
                    $result[$i]['maxEntryDay'] = intval(explode('-', $dt->format('Y-m-d'))[2]);

                    if ($data[$i]->getStartDate() != '') {
                        $result[$i]['maxEntryMonth'] = $months[intval(explode('-', $dt->format('Y-m-d'))[1]) - 1];
                    } else {
                        $result[$i]['maxEntryMonth'] = '';
                    }

                    $result[$i]['startDate'] = $data[$i]->getStartDate();
                    $result[$i]['endDate'] = $data[$i]->getEndDate();
                    $result[$i]['endDay'] = intval(explode('/', $data[$i]->getEndDate())[0]);
                    $result[$i]['lot'] = $data[$i]->getlot();

                    if ($data[$i]->getStartDate() != '') {
                        $result[$i]['monthText'] = $months[intval(explode('/', $data[$i]->getStartDate())[1]) - 1];
                    } else {
                        $result[$i]['monthText'] = '';
                    }

                    $result[$i]['size'] = $sizeofData;
                    $result[$i]['checkOriginal'] = true;
                    if (sizeof(explode('_', $data[$i]->getLot())) == 3) {
                        $result[$i]['lotText'] = ucfirst(strtolower(explode('_', $data[$i]->getLot())[0])) . ' ' . explode('_', $data[$i]->getLot())[1] . ' ' . ucfirst(strtolower(explode('_', $data[$i]->getLot())[2]));
                        $result[$i]['lotNum'] = explode('_', $data[$i]->getLot())[1];
                    } else {
                        $result[$i]['lotNum'] = explode('_', $data[$i]->getLot())[1];
                        $result[$i]['lotText'] = ucfirst(strtolower(explode('_', $data[$i]->getLot())[0])) . ' ' . explode('_', $data[$i]->getLot())[1];
                    }
                } else {
                    if ($data[$i]->getStartDate() && $data[$i]->getEndDate() && explode('/', $data[$i]->getStartDate())[2] == $currrentYear) {
                        $dt = new \DateTime(intval(explode('/', $data[$i]->getStartDate())[2]) . '-' .
                                            intval(explode('/', $data[$i]->getStartDate())[1]) . '-' .
                                            intval(explode('/', $data[$i]->getStartDate())[0]));

                        $dt = $dt->modify('-' . $maxEntryDate . ' days');

                        $result[$i]['startDay'] = intval(explode('/', $data[$i]->getStartDate())[0]);

                        if ($data[$i]->getStartDate() != '') {
                            $result[$i]['maxEntryMonth'] = $months[intval(explode('-', $dt->format('Y-m-d'))[1]) - 1];
                        } else {
                            $result[$i]['maxEntryMonth'] = '';
                        }

                        $result[$i]['maxDate'] = explode('-', $dt->format('Y-m-d'))[2] . '/' .
                                                 explode('-', $dt->format('Y-m-d'))[1] . '/' .
                                                 explode('-', $dt->format('Y-m-d'))[0];

                        $result[$i]['startDate'] = $result[$i]['maxDate'];
                        $result[$i]['endDate'] = $result[$i]['maxDate'];

                        $result[$i]['endDay'] = intval(explode('/', $data[$i]->getEndDate())[0]);
                        $result[$i]['lot'] = $data[$i]->getlot();

                        if ($data[$i]->getStartDate() != '') {
                            $result[$i]['monthText'] = $months[intval(explode('/', $data[$i]->getStartDate())[1]) - 1];
                        } else {
                            $result[$i]['monthText'] = '';
                        }

                        $result[$i]['size'] = $sizeofData;
                        $result[$i]['checkOriginal'] = false;
                        if (sizeof(explode('_', $data[$i]->getLot())) == 3) {
                            $result[$i]['lotText'] = ucfirst(strtolower(explode('_', $data[$i]->getLot())[0])) . ' ' . explode('_', $data[$i]->getLot())[1] . ' ' . ucfirst(strtolower(explode('_', $data[$i]->getLot())[2]));
                            $result[$i]['lotNum'] = explode('_', $data[$i]->getLot())[1];
                        } else {
                            $result[$i]['lotText'] = ucfirst(strtolower(explode('_', $data[$i]->getLot())[0])) . ' ' . explode('_', $data[$i]->getLot())[1];
                            $result[$i]['lotNum'] = explode('_', $data[$i]->getLot())[1];
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function getByBibliotecaId()
    {
        $session = $this->container->get('session');
        $user_info = $session->get('user_info');

        $entityInstance = $this->container->get('doctrine')->getRepository(Localizations::class);
        $libraryEns = $entityInstance->findOneBy(['id' => $user_info['library']->getCode()])->getEns();

        $data = $entityInstance->findOneBy(['ens' => $libraryEns])->getRoute();

        $shipmentData = [];

        if ($data) {
            $entityInstance = $this->container->get('doctrine')->getRepository(ShipmentsMiddle::class);
            $ruta = $entityInstance->findOneBy(['id' => $data])->getTramDesc();
            $shipmentData = $this->getShipmentsByRuta($ruta);
        }

        return $shipmentData;
    }
}
