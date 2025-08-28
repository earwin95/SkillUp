<?php
// src/Entity/ExchangeRequest.php

namespace App\Entity;

use App\Enum\ExchangeRequestStatus;
use App\Repository\ExchangeRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExchangeRequestRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_pending_request_per_user_offer',
    columns: ['requester_id', 'offer_id', 'status']
)]
class ExchangeRequest
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    // Qui envoie la demande
    #[ORM\ManyToOne(inversedBy: 'exchangeRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $requester = null;

    // Sur quelle offre
    #[ORM\ManyToOne(inversedBy: 'exchangeRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Offer $offer = null;

#[ORM\Column(enumType: ExchangeRequestStatus::class)]
private ExchangeRequestStatus $status = ExchangeRequestStatus::PENDING;

    #[Assert\Length(max: 1000)]
    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $message = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters/Setters...

    public function getId(): ?int { return $this->id; }

    public function getRequester(): ?User { return $this->requester; }
    public function setRequester(?User $requester): self { $this->requester = $requester; return $this; }

    public function getOffer(): ?Offer { return $this->offer; }
    public function setOffer(?Offer $offer): self { $this->offer = $offer; return $this; }

    public function getStatus(): ExchangeRequestStatus { return $this->status; }
    public function setStatus(ExchangeRequestStatus $status): self { $this->status = $status; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): self { $this->message = $message; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $d): self { $this->createdAt = $d; return $this; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $d): self { $this->updatedAt = $d; return $this; }
}
