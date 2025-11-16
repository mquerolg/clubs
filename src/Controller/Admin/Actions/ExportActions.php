<?php

namespace App\Controller\Admin\Actions;

use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ExportActions
 */
trait ExportActions
{
    /**
     * Name of the header when exporting the csv file
     *
     * @var string
     */
    private $_fileNameExportData = 'export_data';

    /**
     * Generates data extraction from a table to generate a csv under the same filters
     *
     * @param  AdminContext $context
     *
     * @return Response
     */
    public function exportAction(AdminContext $context): Response
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $entity = $context->getEntity();
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));

        $this->container->get(EntityFactory::class)->processFields($entity, $fields);

        $filters = $this->container->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $entity);
        $queryBuilder = $this->createIndexQueryBuilder($context->getSearch(), $entity, $fields, $filters);
        $data = array_map(function ($row) use ($entity, $fields) {
            $data = [];
            foreach ($fields as $field) {
                $method = 'get' . ucfirst($field->getProperty());
                if (method_exists($row, $method)) {
                    $value = $row->{$method}();
                    $data[$field->getLabel()] = $this->formatExportValue($value);
                }
            }
            return $data;
        }, $queryBuilder->getQuery()->getResult());

        // Crear una nueva hoja de cálculo y establecer los datos
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Agregar encabezados
        if (!empty($data)) {
            $headers = array_keys(reset($data));
            $sheet->fromArray($headers, null, 'A1');

            // Formato de encabezado
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF891536'], // Color de fondo (#891536)
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

            // Agregar datos
            $sheet->fromArray($data, null, 'A2');

            // Ajustar el ancho de las columnas
            foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Colores alternos para las filas
            $rowCount = count($data);
            for ($row = 2; $row <= $rowCount + 1; $row++) {
                $fillType = ($row % 2 == 0) ? 'FFEEEEEE' : 'FFFFFFFF'; // Alterna entre un color claro y blanco
                $sheet->getStyle('A' . $row . ':' . $sheet->getHighestColumn() . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($fillType);
            }
        }

        // Crear el archivo Excel
        $filename = $this->getFileNameExportData() . '_' . date_create()->format('d-m-y') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        // Crear una respuesta HTTP
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }

    private function formatExportValue($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('d/m/Y');
        }
        if (is_object($value)) {
            return $value->__toString();
        }
        return $value;
    }

    /**
     * get data from form and store in session
     *
     * @param  mixed $context
     * @return Response
     */
    public function columnsAction(AdminContext $context): Response
    {
        $request = $context->getRequest();
        $session = $request->getSession();
        $tables = $session->has('tables') ? $session->get('tables') : [];

        $tables[$this->getEntityFqcn()] = $request->get('columns');

        $session->set('tables', $tables);

        return $this->redirect($this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->unset(EA::ENTITY_ID)->generateUrl());
    }

    /**
     * set columnsAction to crud
     *
     * @return Action
     */
    public function getColumnsAction(): Action
    {
        return Action::new('columnsAction', '', 'fa fa-list')
            ->linkToCrudAction('columnsAction')
            ->setCssClass('btn btn-secondary')
            ->createAsGlobalAction();
    }

    /*
    |--------------------------------------------------------------------------
    | GETTER'S & SETTER'S
    |--------------------------------------------------------------------------
    */

    /**
     * Get the value of _fileNameExportData
     */
    public function getFileNameExportData()
    {
        return $this->_fileNameExportData;
    }

    /**
     * Set the value of _fileNameExportData
     *
     * @return  self
     */
    public function setFileNameExportData($_fileNameExportData)
    {
        $this->_fileNameExportData = $_fileNameExportData;

        return $this;
    }
}
