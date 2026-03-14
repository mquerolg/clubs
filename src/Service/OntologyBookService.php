<?php

namespace App\Service;

use App\Entity\OntologyBook;

/**
 * OntologyBookService - Service to parse and read books from clubsdeletura.owl
 */
class OntologyBookService
{
    private string $owlFilePath;
    private array $books = [];
    private bool $loaded = false;

    public function __construct(string $projectDir)
    {
        $this->owlFilePath = $projectDir . '/ontologia/clubsdeletura.owl';
    }

    /**
     * Load and parse the OWL file
     */
    private function loadBooks(): void
    {
        if ($this->loaded) {
            return;
        }

        if (!file_exists($this->owlFilePath)) {
            throw new \RuntimeException("OWL file not found at: " . $this->owlFilePath);
        }

        $content = file_get_contents($this->owlFilePath);
        $this->parseOwlContent($content);
        $this->loaded = true;
    }

    /**
     * Parse OWL file content and extract book instances
     */
    private function parseOwlContent(string $content): void
    {
        // Find all book declarations using regex
        preg_match_all('/<NamedIndividual abbreviatedIRI="(ex:book\d+)"\/>/  ', $content, $bookMatches);
        
        $bookIds = array_unique($bookMatches[1]);
        
        foreach ($bookIds as $bookId) {
            $book = new OntologyBook();
            $book->setId($bookId);
            
            // Extract title from DataPropertyAssertion with schema1:name
            $pattern = '/<DataPropertyAssertion>\s*<DataProperty abbreviatedIRI="schema1:name"\/>\s*<NamedIndividual abbreviatedIRI="' . preg_quote($bookId, '/') . '"\/>\s*<Literal>([^<]+)<\/Literal>\s*<\/DataPropertyAssertion>/s';
            if (preg_match($pattern, $content, $titleMatch)) {
                $book->setTitle(trim($titleMatch[1]));
            }
            
            // Extract date published from DataPropertyAssertion with schema1:datePublished  
            $pattern = '/<DataPropertyAssertion>\s*<DataProperty abbreviatedIRI="schema1:datePublished"\/>\s*<NamedIndividual abbreviatedIRI="' . preg_quote($bookId, '/') . '"\/>\s*<Literal>([^<]+)<\/Literal>\s*<\/DataPropertyAssertion>/s';
            if (preg_match($pattern, $content, $dateMatch)) {
                $book->setDatePublished(trim($dateMatch[1]));
            }
            
            // Extract author name from AnnotationAssertion with schema1:authorName
            $pattern = '/<AnnotationAssertion>\s*<AnnotationProperty abbreviatedIRI="schema1:authorName"\/>\s*<AbbreviatedIRI>' . preg_quote($bookId, '/') . '<\/AbbreviatedIRI>\s*<Literal>([^<]+)<\/Literal>\s*<\/AnnotationAssertion>/s';
            if (preg_match($pattern, $content, $authorMatch)) {
                $book->setAuthorName(trim($authorMatch[1]));
            }
            
            // Extract genre from AnnotationAssertion with schema1:genre
            $pattern = '/<AnnotationAssertion>\s*<AnnotationProperty abbreviatedIRI="schema1:genre"\/>\s*<AbbreviatedIRI>' . preg_quote($bookId, '/') . '<\/AbbreviatedIRI>\s*<Literal>([^<]+)<\/Literal>\s*<\/AnnotationAssertion>/s';
            if (preg_match($pattern, $content, $genreMatch)) {
                $book->setGenre(trim($genreMatch[1]));
            }
            
            // Extract rating from AnnotationAssertion with schema1:aggregateRating
            $pattern = '/<AnnotationAssertion>\s*<AnnotationProperty abbreviatedIRI="schema1:aggregateRating"\/>\s*<AbbreviatedIRI>' . preg_quote($bookId, '/') . '<\/AbbreviatedIRI>\s*<AbbreviatedIRI>(ex:rating\d+)<\/AbbreviatedIRI>\s*<\/AnnotationAssertion>/s';
            if (preg_match($pattern, $content, $ratingMatch)) {
                $ratingId = $ratingMatch[1];
                // Now extract the ratingValue for this rating
                $valuePattern = '/<AnnotationAssertion>\s*<AnnotationProperty abbreviatedIRI="schema1:ratingValue"\/>\s*<AbbreviatedIRI>' . preg_quote($ratingId, '/') . '<\/AbbreviatedIRI>\s*<Literal[^>]*>(\d+)<\/Literal>\s*<\/AnnotationAssertion>/s';
                if (preg_match($valuePattern, $content, $valueMatch)) {
                    $book->setRating((int)$valueMatch[1]);
                }
            }
            
            // Extract description from AnnotationAssertion with schema1:description
            $pattern = '/<AnnotationAssertion>\s*<AnnotationProperty abbreviatedIRI="schema1:description"\/>\s*<AbbreviatedIRI>' . preg_quote($bookId, '/') . '<\/AbbreviatedIRI>\s*<Literal>([^<]+)<\/Literal>\s*<\/AnnotationAssertion>/s';
            if (preg_match($pattern, $content, $descriptionMatch)) {
                // Decode HTML entities that might be present in the description
                $description = html_entity_decode(trim($descriptionMatch[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $book->setDescription($description);
            }
                        // Only add book if it has at least a title
            if ($book->getTitle()) {
                $this->books[$bookId] = $book;
            }
        }
    }

    /**
     * Get all books
     * 
     * @return OntologyBook[]
     */
    public function getAllBooks(): array
    {
        $this->loadBooks();
        return array_values($this->books);
    }

    /**
     * Get a book by ID
     */
    public function getBookById(string $id): ?OntologyBook
    {
        $this->loadBooks();
        return $this->books[$id] ?? null;
    }

    /**
     * Search books by title, author, or genre
     * 
     * @return OntologyBook[]
     */
    public function searchBooks(string $query): array
    {
        $this->loadBooks();
        $query = strtolower($query);
        
        return array_filter($this->books, function(OntologyBook $book) use ($query) {
            return stripos($book->getTitle(), $query) !== false
                || stripos($book->getAuthorName(), $query) !== false
                || stripos($book->getGenre(), $query) !== false;
        });
    }

    /**
     * Get books sorted by a field
     * 
     * @return OntologyBook[]
     */
    public function getBooksOrderedBy(string $field, string $direction = 'ASC'): array
    {
        $this->loadBooks();
        $books = $this->books;

        $getter = 'get' . ucfirst($field);
        
        usort($books, function($a, $b) use ($getter, $direction) {
            $aValue = method_exists($a, $getter) ? $a->$getter() : '';
            $bValue = method_exists($b, $getter) ? $b->$getter() : '';
            
            $result = strcasecmp($aValue ?? '', $bValue ?? '');
            return $direction === 'DESC' ? -$result : $result;
        });

        return $books;
    }

    /**
     * Get unique genres
     * 
     * @return string[]
     */
    public function getUniqueGenres(): array
    {
        $this->loadBooks();
        $genres = array_map(fn($book) => $book->getGenre(), $this->books);
        $genres = array_filter($genres);
        $genres = array_unique($genres);
        sort($genres);
        return array_values($genres);
    }
}
