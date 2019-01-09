<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookRepository")
 */
class Book
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $author;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $addedDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Image(
     *     mimeTypes={"image/png", "image/jpeg"}
     * )
     */
    private $coverImage;

    /**
     * @ORM\Column(type = "string", length = 255, nullable = true)
     * @Assert\File(
     *     mimeTypes = {"application/pdf"},
     *     maxSize = "5Mi",
     *     groups = {"new"}
     * )
     */
    private $file;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $downloadable;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAddedDate(): ?\DateTimeInterface
    {
        return $this->addedDate;
    }

    public function setAddedDate(\DateTimeInterface $addedDate): self
    {
        $this->addedDate = $addedDate;

        return $this;
    }

    public function getCoverImage()
    {
        return $this->coverImage;
    }

    public function setCoverImage($coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getDownloadable(): ?bool
    {
        return $this->downloadable;
    }

    public function setDownloadable(?bool $downloadable): self
    {
        $this->downloadable = $downloadable;

        return $this;
    }

    public function isDownloadable(): bool
    {
        return false !== $this->downloadable;
    }

    // custom validation function for Book class

    /**
     * @Assert\Callback()
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (false) {
            $context
                ->buildViolation('')
                ->atPath('title')
                ->addViolation()
            ;
        }
    }
}
