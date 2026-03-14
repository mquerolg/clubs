<?php

namespace App\Entity;

/**
 * OntologyBook - Represents a book from the clubsdeletura.owl ontology
 * This is a non-persisted entity that reads data from the OWL file
 */
class OntologyBook
{
    private ?string $id = null;
    private ?string $title = null;
    private ?string $authorName = null;
    private ?string $genre = null;
    private ?string $datePublished = null;

    public function __construct()
    {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(?string $authorName): self
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;
        return $this;
    }

    public function getDatePublished(): ?string
    {
        return $this->datePublished;
    }

    public function setDatePublished(?string $datePublished): self
    {
        $this->datePublished = $datePublished;
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
