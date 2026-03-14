<?php

namespace App\Controller\Admin;

use App\Controller\SecurityController;
use App\Entity\OntologyBook;
use App\Service\OntologyBookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * OntologyBookCrudController - Controller for viewing books from the ontology
 * 
 * @Route("/admin/ontology-books", name="admin_ontology_books_")
 */
class OntologyBookCrudController extends AbstractController
{
    private OntologyBookService $ontologyBookService;

    public function __construct(OntologyBookService $ontologyBookService)
    {
        $this->ontologyBookService = $ontologyBookService;
    }

    /**
     * Check if user has access
     */
    private function checkAccess(): bool
    {
        $session = $this->container->get('session');
        // Allow access if user is admin or has valid user_info (authenticated user)
        return SecurityController::isAdmin($session) || $session->has('user_info');
    }

    /**
     * Index action to display books from OWL file
     * 
     * @Route("", name="index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        // Check access permissions
        if (!$this->checkAccess()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Get filter and sort parameters
        $searchQuery = $request->query->get('query', '');
        $sortField = $request->query->get('sort', 'title');
        $sortDirection = $request->query->get('order', 'ASC');
        $page = max(1, (int)$request->query->get('page', 1));
        $pageSize = 20;

        // Get books
        if ($searchQuery) {
            $allBooks = $this->ontologyBookService->searchBooks($searchQuery);
        } else {
            $allBooks = $this->ontologyBookService->getBooksOrderedBy($sortField, $sortDirection);
        }

        // Manual pagination
        $totalBooks = count($allBooks);
        $totalPages = max(1, ceil($totalBooks / $pageSize));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $pageSize;
        $books = array_slice($allBooks, $offset, $pageSize);

        // Get unique genres for filtering
        $genres = $this->ontologyBookService->getUniqueGenres();

        return $this->render('crud/index/ontology_books.html.twig', [
            'books' => $books,
            'genres' => $genres,
            'totalBooks' => $totalBooks,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pageSize' => $pageSize,
            'searchQuery' => $searchQuery,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
        ]);
    }

    /**
     * Detail action to show book details
     * 
     * @Route("/{bookId}", name="detail", methods={"GET"})
     */
    public function detail(string $bookId): Response
    {
        // Check access permissions
        if (!$this->checkAccess()) {
            return $this->redirectToRoute('app_login');
        }

        $book = $this->ontologyBookService->getBookById($bookId);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        return $this->render('crud/detail/ontology_book.html.twig', [
            'book' => $book,
        ]);
    }
}
