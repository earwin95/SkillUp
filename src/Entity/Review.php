<?php
// src/Entity/Review.php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_author_subject_exchange',
    columns: ['author_id', 'subject_user_id', 'exchange_request_id']
)]
class Review
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    // qui écrit
    #[ORM\ManyToOne(inversedBy: 'reviewsAuthored')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $author = null;

    // qui est noté
    #[ORM\ManyToOne(inversedBy: 'reviewsReceived')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $subjectUser = null;

    // contexte (optionnel)
    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Offer $offer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ExchangeRequest $exchangeRequest = null;

    #[Assert\NotNull]
    #[Assert\Range(min: 1, max: 5)]
    #[ORM\Column(type: 'smallint')]
    private int $rating = 5;

    #[Assert\Length(max: 2000)]
    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }

    // Getters/Setters...
    public function getId(): ?int { return $this->id; }

    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $author): self { $this->author = $author; return $this; }

    public function getSubjectUser(): ?User { return $this->subjectUser; }
    public function setSubjectUser(?User $subjectUser): self { $this->subjectUser = $subjectUser; return $this; }

    public function getOffer(): ?Offer { return $this->offer; }
    public function setOffer(?Offer $offer): self { $this->offer = $offer; return $this; }

    public function getExchangeRequest(): ?ExchangeRequest { return $this->exchangeRequest; }
    public function setExchangeRequest(?ExchangeRequest $er): self { $this->exchangeRequest = $er; return $this; }

    public function getRating(): int { return $this->rating; }
    public function setRating(int $rating): self { $this->rating = $rating; return $this; }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $comment): self { $this->comment = $comment; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $d): self { $this->createdAt = $d; return $this; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $d): self { $this->updatedAt = $d; return $this; }
}
