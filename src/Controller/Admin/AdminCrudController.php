<?php

namespace App\Controller\Admin;

use App\Controller\SecurityController;
use App\Diba\Helpers\OptionsHelper as Helper;
use App\Entity\Deleted\ClubsDeleted;
use App\Entity\Deleted\LotsDeleted;
use App\Entity\Libraries;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\PaginatorFactory;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Admin\Actions\ExportActions;

/**
 * AdminCrudController
 *
 * This is an intermediate class between AbstracCrudController and the
 * rest of Cruds to simplify their syntax and avoid function redeclaration
 */
abstract class AdminCrudController extends AbstractCrudController
{
    use ExportActions;

    /**
     * Name of the template associated with Index
     *
     * @var string
     */
    private $_templateIndexName = 'crud/index';

    /**
     * Name of the template associated with Edit
     *
     * @var string
     */
    private $_templateEditName = 'crud/edit';

    /**
     * Name of the template associated with New
     *
     * @var string
     */
    private $_templateNewName = 'crud/new';

    /**
     * Name of the template associated with Detail
     *
     * @var string
     */
    private $_templateDetailName = 'crud/detail';

    /**
     * If the value is false, admin access is restricted.
     *
     * @var string
     */
    private $_adminAccess = false;

    /**
     * If the value is false, user access is restricted.
     *
     * @var bool
     */
    private $_userAccess = false;

    /**
     * Contains the titles of the view headers
     *
     * @var array
     */
    private $_titels = [];

    /**
     * If the value is true, set custom orderBy.
     *
     * @var bool
     */
    private $_sortable = false;

    /**
     * The custom default orderBy.
     *
     * @var array
     */
    private $_default_sort = null;

    /*
    |--------------------------------------------------------------------------
    | CRUD
    |--------------------------------------------------------------------------
    */

    /**
     * Extended function of AbstractCrudController,
     * to generate the names of the view headers
     *
     * @param  Crud $crud
     *
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        foreach ($this->_titels as $page => $titel) {
            $crud = $crud->setPageTitle($page, $titel);
        }

        if (!is_null($this->_default_sort)) {
            $crud = $crud->setDefaultSort($this->_default_sort);
        }

        return $crud;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    /**
     * AbstractCrudController extended function, to add configCustomResponse actions
     *
     * @param  SearchDto $searchDto
     * @param  EntityDto $entityDto
     * @param  FieldCollection $fields
     * @param  FilterCollection $filters
     *
     * @return QueryBuilder
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $this->configCustomResponse($queryBuilder);
    }

    /**
     * Function that allows the insertion of relationals in the QueryBuilder
     * preventing incompatibility or duplication errors.
     *
     * @param  QueryBuilder $response
     * @param  array $joins
     *
     * @return QueryBuilder
     */
    public function addJoin(QueryBuilder $response, array $joins): QueryBuilder
    {
        $alias = [];

        if (!empty($response->getDQLPart('join')) && isset($response->getDQLPart('join')['entity'])) {
            foreach ($response->getDQLPart('join')['entity'] as $join) {
                $alias[] = $join->getAlias();
            }
        }

        foreach ($joins as $join => $label) {
            if (!in_array($label, $alias)) {
                $response->join($join, $label);
            }
        }

        return $response;
    }

    /**
     * Modify the general query for the results of the table results
     *
     * @param  QueryBuilder $response
     *
     * @return QueryBuilder
     */
    public function configCustomResponse(QueryBuilder $response): QueryBuilder
    {
        return $response;
    }

    /**
     * Modify the parameters that are sent to the view Index
     *
     * @param  AdminContext $context Use only for extended function
     * @param  array $responseParameters
     *
     * @return array
     */
    public function configureIndexResponseParameters(AdminContext $context, array $responseParameters): array
    {
        return $responseParameters;
    }

    /**
     * Modify the parameters that are sent to the view Detail
     *
     * @param  AdminContext $context Use only for extended function
     * @param  array $responseParameters
     *
     * @return array
     */
    public function configureDetailResponseParameters(AdminContext $context, array $responseParameters): array
    {
        return $responseParameters;
    }

    /**
     * Modify the parameters that are sent to the view Edit
     *
     * @param  AdminContext $context Use only for extended function
     * @param  array $responseParameters
     *
     * @return array
     */
    public function configureEditResponseParameters(AdminContext $context, array $responseParameters): array
    {
        return $responseParameters;
    }

    /**
     * Modify the parameters that are sent to the view New
     *
     * @param  AdminContext $context Use only for extended function
     * @param  array $responseParameters
     *
     * @return array
     */
    public function configureNewResponseParameters(AdminContext $context, array $responseParameters): array
    {
        return $responseParameters;
    }

    /*
    |--------------------------------------------------------------------------
    | REQUEST
    |--------------------------------------------------------------------------
    */

    /**
     * Allows you to modify the fields that come from the form when editing entity
     *
     * @param  Request $request
     * @param  AdminContext $context Use only for extended function
     *
     * @return array
     */
    public function configureEditRequest(Request $request, AdminContext $context): array
    {
        return $request->request->all();
    }

    /**
     * Allows you to modify the fields that come from the form when create entity
     *
     * @param  Request $request
     * @param  AdminContext $context Use only for extended function
     *
     * @return array
     */
    public function configureNewRequest(Request $request, AdminContext $context): array
    {
        return $request->request->all();
    }

    /*
    |--------------------------------------------------------------------------
    | INSTANCES
    |--------------------------------------------------------------------------
    */

    /**
     * Allows to modify an entity before saving the fields in the DB
     *
     * @param  $entityInstance
     *
     * @return $entityInstance
     */
    public function configureNewInstance($entityInstance)
    {
        return $entityInstance;
    }

    /**
     * Allows to modify an entity before edit the fields in the DB
     *
     * @param  $entityInstance
     *
     * @return $entityInstance
     */
    public function configureEditInstance($entityInstance)
    {
        $entityInstance->setUpdatedAt(new \DateTime('now'));

        return $entityInstance;
    }

    /**
     * Allows to modify an entity before softdeleted in the DB
     *
     * @param  $entityInstance
     *
     * @return $entityInstance
     */
    public function configureDeleteInstance($entityInstance)
    {
        return $entityInstance;
    }

    /**
     * Allows you to make changes to an entity after it is created
     *
     * @param  EntityManagerInterface $entityManager
     * @param  $entityInstance
     *
     * @return $entityInstance
     */
    public function configureAfterNewInstance(EntityManagerInterface $entityManager, $entityInstance)
    {
        return $entityInstance;
    }

    /**
     * Allows you to make changes to an entity after it is updated
     *
     * @param  EntityManagerInterface $entityManager Use only for extended function
     * @param  $entityInstance
     *
     * @return $entityInstance
     */
    public function configureAfterEditInstance(EntityManagerInterface $entityManager, $entityInstance)
    {
        return $entityInstance;
    }

    /*
    |--------------------------------------------------------------------------
    | SORTING
    |--------------------------------------------------------------------------
    */

    /**
     * Override the orderBy in a custom QueryBuilder
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     */
    public function getOrderBy(QueryBuilder $queryBuilder): QueryBuilder
    {
        if (!$this->_sortable) {
            return $queryBuilder;
        }

        $parts = $queryBuilder->getDQLParts();

        if (isset($parts['orderBy'], $parts['orderBy'][0], $parts['orderBy'][0]->getParts()[0])) {
            $sorts = explode(' ', $parts['orderBy'][0]->getParts()[0]);

            if (isset($sorts[0], $sorts[1])) {
                if (isset($parts['join'], $parts['join']['entity'])) {
                    foreach ($parts['join']['entity'] as $join) {
                        if ($sorts[0] == $join->getJoin()) {
                            $queryBuilder = $this->replaceJoinOrderBy($queryBuilder, $join->getAlias(), $sorts[0], $sorts[1]);

                            break;
                        }
                    }
                } else {
                    $sort = $this->makeSort($sorts[0]);

                    $queryBuilder->orderBy($sort, $sorts[1]);
                }
            }
        }

        return $queryBuilder;
    }

    /**
     * Generates a string for Oracle, with character replacement
     *
     * @param string $sort
     *
     * @return string
     */
    public function makeSort(string $sort): string
    {
        $replaces = [
            'Á' => 'A',
            'À' => 'A',
            'Ä' => 'A',
            'É' => 'E',
            'È' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ì' => 'I',
            'Ï' => 'I',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ö' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Ü' => 'U',
        ];

        $sort = 'UPPER(' . $sort . ')';

        foreach ($replaces as $key => $value) {
            $sort = "REPLACE($sort,'$key','$value')";
        }

        return $sort;
    }

    /**
     * Returns the appropriate construct for the orderBy in a query join
     *
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $entityAlias
     * @param string $orderBy
     *
     * @return QueryBuilder
     */
    public function replaceJoinOrderBy(QueryBuilder $queryBuilder, string $alias, string $entityAlias, string $orderBy): QueryBuilder
    {
        return $queryBuilder;
    }

    /*
    |--------------------------------------------------------------------------
    | FIELDS
    |--------------------------------------------------------------------------
    */

    /**
     * Extended function of parent AbstractCrudController.
     * Add a conditional based on user role.
     *
     * @param  string $pageName
     *
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        if (!$this->isGrantedAccess()) {
            return [];
        }

        return $this->isAdmin() ?
                $this->configureAdminFields($pageName)
            :
                $this->configureUserFields($pageName)
        ;
    }

    /**
     * determine if field is visible
     *
     * @param  mixed $fields
     * @return iterable
     */
    public function determineVisibleFields($fields): iterable
    {
        $name = $this->getEntityFqcn();
        $session = $session = $this->container->get('session');
        $tables = $session->get('tables');

        if (isset($tables[$name])) {
            foreach ($fields as $field) {
                $key = $field->getAsDto()->getProperty();

                if (!in_array($key, $tables[$name])) {
                    $field->hideOnIndex();
                } else {
                    $field->onlyOnIndex();
                }
            }
        }

        return $fields;
    }

    /**
     * Intermediate function of configureFields, which generates a conditional
     * by type of page in the case of the administrator role.
     *
     * @param  string $pageName
     *
     * @return iterable
     */
    public function configureAdminFields(string $pageName): iterable
    {
        switch ($pageName) {
            case Crud::PAGE_INDEX:
                return $this->determineVisibleFields($this->configureAdminIndexFields());
                break;
            case Crud::PAGE_DETAIL:
                return $this->configureAdminDetailFields();
                break;
            case Crud::PAGE_EDIT:
                return $this->configureAdminEditFields();
                break;
            case Crud::PAGE_NEW:
                return $this->configureAdminNewFields();
                break;
            default:
                return $this->configureAdminDefaultFields();
        }
    }

    /**
     * Intermediate function of configureFields, which generates a conditional
     * by type of page in the case of the user role
     *
     * @param  string $pageName
     *
     * @return iterable
     */
    public function configureUserFields(string $pageName): iterable
    {
        switch ($pageName) {
            case Crud::PAGE_INDEX:
                return $this->determineVisibleFields($this->configureUserIndexFields());
                break;
            case Crud::PAGE_DETAIL:
                return $this->configureUserDetailFields();
                break;
            case Crud::PAGE_EDIT:
                return $this->configureUserEditFields();
                break;
            case Crud::PAGE_NEW:
                return $this->configureUserNewFields();
                break;
            default:
                return $this->configureUserDefaultFields();
        }
    }

    /**
     * Map the fields visible to the administrator in the Index view
     *
     * @return iterable
     */
    public function configureAdminIndexFields(): iterable
    {
        return [];
    }

    /**
     * Map the fields visible to the administrator in the Detail view
     *
     * @return iterable
     */
    public function configureAdminDetailFields(): iterable
    {
        return [];
    }

    /**
     * Map the fields visible to the administrator in the Edit view
     *
     * @return iterable
     */
    public function configureAdminEditFields(): iterable
    {
        return $this->configureAdminNewFields();
    }

    /**
     * Map the fields visible to the administrator in the New view
     *
     * @return iterable
     */
    public function configureAdminNewFields(): iterable
    {
        return [];
    }

    /**
     * Map the fields visible to the administrator in custom views
     *
     * @return iterable
     */
    public function configureAdminDefaultFields(): iterable
    {
        return [];
    }

    /**
     * Map the fields visible to the user in Index views
     *
     * @return iterable
     */
    public function configureUserIndexFields(): iterable
    {
        return [];
    }

    /**
     * Map the fields visible to the user in Detail views
     *
     * @return iterable
     */
    public function configureUserDetailFields(): iterable
    {
        return [];
    }

    /**
     * Map the fields visible to the user in Edit views
     *
     * @return iterable
     */
    public function configureUserEditFields(): iterable
    {
        return $this->configureUserNewFields();
    }

    /**
     * Map the fields visible to the user in New views
     *
     * @return iterable
     */
    public function configureUserNewFields(): iterable
    {
        return [];
    }

    /**
     * Map the fields visible to the user in custom views
     *
     * @return iterable
     */
    public function configureUserDefaultFields(): iterable
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | VIEWS
    |--------------------------------------------------------------------------
    */

    /**
     * Generate the response for the Index view
     *
     * @param  AdminContext $context
     *
     * @return KeyValueStore
     */
    public function index(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filters = $this->container->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $queryBuilder = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);
        $queryBuilder = $this->getOrderBy($queryBuilder);
        $paginator = $this->container->get(PaginatorFactory::class)->create($queryBuilder);

        // this can happen after deleting some items and trying to return
        // to a 'index' page that no longer exists. Redirect to the last page instead
        if ($paginator->isOutOfRange()) {
            return $this->redirect($this->container->get(AdminUrlGenerator::class)
                ->set(EA::PAGE, $paginator->getLastPage())
                ->generateUrl());
        }

        $entities = $this->container->get(EntityFactory::class)->createCollection($context->getEntity(), $paginator->getResults());

        $this->container->get(EntityFactory::class)->processFieldsForAll($entities, $fields);

        $actions = $this->container->get(EntityFactory::class)->processActionsForAll($entities, $context->getCrud()->getActionsConfig());

        $responseParameters = [
            'pageName' => Crud::PAGE_INDEX,
            'table_fields' => $fields,
            'templateName' => $this->getTemplateIndexName(),
            'entities' => $entities,
            'paginator' => $paginator,
            'global_actions' => $actions->getGlobalActions(),
            'batch_actions' => $actions->getBatchActions(),
            'filters' => $filters,
            'pageSize' => $this->getPageSize(),
        ];

        return $this->configureResponseParameters(KeyValueStore::new($this->configureIndexResponseParameters($context, $responseParameters)));
    }

    /**
     * Generate the response for the Detail view
     *
     * @param  AdminContext $context
     *
     * @return KeyValueStore
     */
    public function detail(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_DETAIL)));
        $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());

        $entityInstance = $context->getEntity()->getInstance();
        $updated_at = (method_exists($entityInstance, 'getUpdatedFormat')) ? $entityInstance->getUpdatedFormat() : '';

        $responseParameters = [
            'pageName' => Crud::PAGE_DETAIL,
            'templateName' => $this->getTemplateDetailName(),
            'entity' => $context->getEntity(),
            'updated_at' => $updated_at,
            'isAdmin' => $this->isAdmin(),
        ];

        return $this->configureResponseParameters(KeyValueStore::new($this->configureDetailResponseParameters($context, $responseParameters)));
    }

    /**
     * Generate the response for the Edit view
     *
     * @param  AdminContext $context
     *
     * @return KeyValueStore
     */
    public function edit(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_EDIT)));
        $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());

        $entityInstance = $context->getEntity()->getInstance();
        $updated_at = method_exists($entityInstance, 'getUpdatedFormat') ? $entityInstance->getUpdatedFormat() : '';

        if ($context->getRequest()->isXmlHttpRequest()) {
            $fieldName = $context->getRequest()->query->get('fieldName');
            $newValue = 'true' === mb_strtolower($context->getRequest()->query->get('newValue'));

            $event = $this->ajaxEdit($context->getEntity(), $fieldName, $newValue);
            if ($event->isPropagationStopped()) {
                return $event->getResponse();
            }

            // cast to integer instead of string to avoid sending empty responses for 'false'
            return new Response((int) $newValue);
        }

        $editForm = $this->createEditForm($context->getEntity(), $context->getCrud()->getEditFormOptions(), $context);
        $request = $context->getRequest();

        $request->request->replace($this->configureEditRequest($request, $context));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->processUploadedFiles($editForm);

            $entityInstance = $this->configureEditInstance($entityInstance);

            $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

            return $this->getRedirectResponseAfterSave($context, Action::EDIT);
        }

        $responseParameters = [
            'pageName' => Crud::PAGE_EDIT,
            'templateName' => $this->getTemplateEditName(),
            'edit_form' => $editForm,
            'entity' => $context->getEntity(),
            'updated_at' => $updated_at,
        ];

        return $this->configureResponseParameters(KeyValueStore::new($this->configureEditResponseParameters($context, $responseParameters)));
    }

    /**
     * Generate the response for the New view
     *
     * @param  AdminContext $context
     *
     * @return KeyValueStore
     */
    public function new(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $context->getEntity()->setInstance($this->createEntity($context->getEntity()->getFqcn()));
        $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_NEW)));
        $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());

        $newForm = $this->createNewForm($context->getEntity(), $context->getCrud()->getNewFormOptions(), $context);
        $request = $context->getRequest();

        $request->request->replace($this->configureNewRequest($request, $context));
        $newForm->handleRequest($request);

        $entityInstance = $newForm->getData();

        $context->getEntity()->setInstance($entityInstance);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $this->processUploadedFiles($newForm);

            $managerRegistry = $this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn());
            $entityInstance = $this->configureNewInstance($entityInstance);

            $this->persistEntity($managerRegistry, $entityInstance);
            $this->configureAfterNewInstance($managerRegistry, $entityInstance);
            $context->getEntity()->setInstance($entityInstance);

            return $this->getRedirectResponseAfterSave($context, Action::NEW);
        }

        $responseParameters = [
            'pageName' => Crud::PAGE_NEW,
            'templateName' => $this->getTemplateNewName(),
            'entity' => $context->getEntity(),
            'new_form' => $newForm,
        ];

        return $this->configureResponseParameters(KeyValueStore::new($this->configureNewResponseParameters($context, $responseParameters)));
    }

    /**
     * Generate the response for the Delete view
     *
     * @param  AdminContext $context
     *
     * @return KeyValueStore
     */
    public function delete(AdminContext $context)
    {
        if (!$this->isGrantedAccess()) {
            return $this->grantedRedirect();
        }

        $entity = $context->getEntity()->getInstance();

        if ($this->getEntityFqcn() == 'App\Entity\Lots') {
            $entity = $this->container->get('doctrine')->getRepository(LotsDeleted::class)->find($entity->getId());
        } elseif ($this->getEntityFqcn() == 'App\Entity\Clubs') {
            $entity = $this->container->get('doctrine')->getRepository(ClubsDeleted::class)->find($entity->getId());
        }

        $entityInstance = $this->configureDeleteInstance($entity);
        $entityManager = $this->container->get('doctrine')->getManagerForClass($this->getEntityFqcn());

        $entityInstance->setDeletedAt(new \DateTime('now'));

        $entityManager->persist($entityInstance);
        $entityManager->flush();

        $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

        if (null !== $referrer = $context->getReferrer()) {
            return $this->redirect($referrer);
        }

        return $this->redirect($this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->unset(EA::ENTITY_ID)->generateUrl());
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
     * Get the value of _templateIndexName
     */
    public function getTemplateIndexName()
    {
        return $this->_templateIndexName;
    }

    /**
     * Set the value of _templateIndexName
     *
     * @return  self
     */
    public function setTemplateIndexName($_templateIndexName)
    {
        $this->_templateIndexName = $_templateIndexName;

        return $this;
    }

    /**
     * Get the value of _templateEditName
     */
    public function getTemplateEditName()
    {
        return $this->_templateEditName;
    }

    /**
     * Set the value of _templateEditName
     *
     * @return  self
     */
    public function setTemplateEditName($_templateEditName)
    {
        $this->_templateEditName = $_templateEditName;

        return $this;
    }

    /**
     * Get the value of _templateNewName
     */
    public function getTemplateNewName()
    {
        return $this->_templateNewName;
    }

    /**
     * Set the value of _templateNewName
     *
     * @return  self
     */
    public function setTemplateNewName($_templateNewName)
    {
        $this->_templateNewName = $_templateNewName;

        return $this;
    }

    /**
     * Get the value of _templateDetailName
     */
    public function getTemplateDetailName()
    {
        return $this->_templateDetailName;
    }

    /**
     * Set the value of _templateDetailName
     *
     * @return  self
     */
    public function setTemplateDetailName($_templateDetailName)
    {
        $this->_templateDetailName = $_templateDetailName;

        return $this;
    }

    /**
     * Get the value of _adminAccess
     */
    public function getAdminAccess()
    {
        return $this->_adminAccess;
    }

    /**
     * Set the value of _adminAccess
     *
     * @return  self
     */
    public function setAdminAccess($_adminAccess)
    {
        $this->_adminAccess = $_adminAccess;

        return $this;
    }

    /**
     * Get the value of UserAccess
     */
    public function getUserAccess()
    {
        return $this->_userAccess;
    }

    /**
     * Set the value of userAccess
     *
     * @return  self
     */
    public function setUserAccess($_userAccess)
    {
        $this->_userAccess = $_userAccess;

        return $this;
    }

    /**
     * Set the value of titles
     *
     * @return  self
     */
    public function setTitels(array $titels)
    {
        $this->_titels = $titels;

        return $this;
    }

    /**
     * Get if the value is true, set custom orderBy.
     *
     * @return  bool
     */
    public function getSortable()
    {
        return $this->_sortable;
    }

    /**
     * Set if the value is true, set custom orderBy.
     *
     * @param  bool  $_sortable  If the value is true, set custom orderBy.
     *
     * @return  self
     */
    public function setSortable(bool $_sortable)
    {
        $this->_sortable = $_sortable;

        return $this;
    }

    /**
     * Get the custom default orderBy.
     *
     * @return  array
     */
    public function getDefaultSort()
    {
        return $this->_default_sort;
    }

    /**
     * Set the custom default orderBy.
     *
     * @param  array  $_default_sort  The custom default orderBy.
     *
     * @return  self
     */
    public function setDefaultSort(array $_default_sort)
    {
        $this->_default_sort = $_default_sort;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */

    /**
     * Get user library asociate
     *
     * @return  Libraries|null
     */
    public function getUserLibrary()
    {
        $session = $this->container->get('session');
        $user = SecurityController::getUserInfo($session);

        return is_int($user['library']) && $user['library'] == 0 ? null : $user['library'];
    }

    /**
     * Value role user
     *
     * @return  bool
     */
    public function isAdmin(): bool
    {
        $session = $this->container->get('session');

        return SecurityController::isAdmin($session);
    }

    /**
     * Value access user
     *
     * @return  bool
     */
    protected function isGrantedAccess(): bool
    {
        return $this->isAdmin() ? $this->_adminAccess : $this->_userAccess;
    }

    /**
     * Function that generates a 404 return
     *
     * @return Response
     */
    public function grantedRedirect(): Response
    {
        return new Response('', 404);
    }

    /**
     * Function that determines the results to display in the pagination of an element
     *
     * @return int
     */
    public function getPageSize(): int
    {
        $request = Request::createFromGlobals();
        $session = $this->container->get('session');
        $size = (int)$request->get('pagesize', $session->get('pageSize', 10));

        $session->set('pageSize', $size);

        return $size;
    }

    /*
    |--------------------------------------------------------------------------
    | SUPORT
    |--------------------------------------------------------------------------
    */

    /**
     * Returns the path to the global variable NAS_PATH from the current container
     *
     * @return string
     */
    public function getNasPath(): string
    {
        $project_dir = $this->getParameter('kernel.project_dir');
        $path = preg_replace('/[^\/]+/', '..', $project_dir) . $_ENV['NAS_PATH'];
        $instance_path = $this->getContext()->getEntity()->getInstance()->getUrl();

        if (is_null($instance_path) || empty($instance_path)) {
            $new_path = $path . '/' . date('Y');

            if (!is_dir($new_path)) {
                mkdir($new_path, 0755, true);
            }

            return $new_path;
        }

        $year = Helper::findFile($instance_path, $path);

        return ($year !== false) ?
                $path . '/' . $year
            :
                $path
        ;
    }
}
