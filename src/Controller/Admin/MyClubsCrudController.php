<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Actions\LotsDeletedActions;
use App\Entity\Cruds\MyClubs;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Libraries;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * MyClubsCrudController
 */
class MyClubsCrudController extends AdminCrudController
{
    use LotsDeletedActions;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    private $entityManager;
    private $logger;
    private $security;
    private $requestStack;


    public function __construct(EntityManagerInterface $entityManager, Security $security, LoggerInterface $logger, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->security = $security;
        $this->requestStack = $requestStack;

        $this->setTemplateIndexName('crud/index/my_clubs');
        $this->setAdminAccess(false);
        $this->setUserAccess(true);
        $this->setTemplateEditName('crud/edit/clubs');
        $this->setTitels(['edit' => 'my_clubs']);
    }

    public function index(AdminContext $context): Response
    {
        $library = $this->entityManager->getRepository(Libraries::class)->findBy(['active' => true]);

        if ($library) {
            $this->entityManager->initializeObject($library);
        }

        // $session = $this->requestStack->getCurrentRequest()->getSession();
        // $this->logger->info('Datos de la sesión: ' . print_r($session->all(), true));

        $sessionData = $this->requestStack->getCurrentRequest()->getSession();
        $libraryData = $sessionData->get('user_info')['library'];

        $adjustedId = $libraryData->getId();
        $library = $this->entityManager->getRepository(Libraries::class)->find($adjustedId);

        // $this->logger->info('Biblioteca activa: ' . $library->getName() . ' (ID: ' . $library->getId() . ')');

        if ($library) {
            $this->entityManager->initializeObject($library);
        }

        $entity = $context->getEntity();
        $dataClubs = $library->getClubs();
        foreach ($dataClubs as $club) {
            $this->logger->info('Club asociado: ' . $club->getName());
        }

        return $this->render('crud/index/my_clubs.html.twig', [
            'entity' => $entity,
            'dataClubs' => $dataClubs, // Pasamos los clubes filtrados a la plantilla
        ]);
    }


    private function getDataClubs(AdminContext $context): array
    {
        $entity = $context->getEntity();
        $library = $entity->getInstance();

        if (!$library) {
            $library = $this->entityManager->getRepository(Libraries::class)->findOneBy(['active' => true]);
        }
        // Obtén los clubes asociados a las bibliotecas activas
        return $this->entityManager->getRepository(MyClubs::class)->createQueryBuilder('club')
            ->where('club.library IN (:libraries)')
            ->setParameter('libraries', $activeLibraries)
            ->getQuery()
            ->getResult();
    }

    

    public static function getEntityFqcn(): string
    {
        return MyClubs::class;
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    public function configCustomResponse(QueryBuilder $response): QueryBuilder
    {
        $response = $this->addJoin($response, ['entity.library' => 'library']);

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | FIELDS
    |--------------------------------------------------------------------------
    */

    public function configureUserEditFields(): iterable
    {
        return [
            TextField::new('name', 'name_library_club'),
            IntegerField::new('year', 'create_year'),
            AssociationField::new('library')->setQueryBuilder(function ($queryBuilder) {
                return $queryBuilder->andWhere('entity.active = 1');
            })->setFormTypeOption('disabled', 'disabled'),
            TextField::new('typology'),
            TextareaField::new('description', 'club_description'),
            TextareaField::new('observations'),
            BooleanField::new('active'),
            BooleanField::new('external'),
        ];
    }
}
