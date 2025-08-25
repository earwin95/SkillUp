<?php

namespace App\Entity;

use App\Repository\OfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skill $skillOffered = null;

    #[ORM\ManyToOne(inversedBy: 'requestedOffers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skill $skillRequested = null;

    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: ExchangeRequest::class, orphanRemoval: true)]
    private Collection $exchangeRequests;

    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: Review::class, orphanRemoval: false)]
    private Collection $reviews;

    public function __construct()
    {
        $this->exchangeRequests = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    public function getSkillOffered(): ?Skill
    {
        return $this->skillOffered;
    }

    public function setSkillOffered(?Skill $skillOffered): static
    {
        $this->skillOffered = $skillOffered;
        return $this;
    }

    public function getSkillRequested(): ?Skill
    {
        return $this->skillRequested;
    }

    public function setSkillRequested(?Skill $skillRequested): static
    {
        $this->skillRequested = $skillRequested;
        return $this;
    }

    /** @return Collection<int, ExchangeRequest> */
    public function getExchangeRequests(): Collection
    {
        return $this->exchangeRequests;
    }

    public function addExchangeRequest(ExchangeRequest $er): static
    {
        if (!$this->exchangeRequests->contains($er)) {
            $this->exchangeRequests->add($er);
            $er->setOffer($this);
        }
        return $this;
    }

    public function removeExchangeRequest(ExchangeRequest $er): static
    {
        if ($this->exchangeRequests->removeElement($er)) {
            if ($er->getOffer() === $this) {
                $er->setOffer(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Review> */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $r): static
    {
        if (!$this->reviews->contains($r)) {
            $this->reviews->add($r);
            $r->setOffer($this);
        }
        return $this;
    }

    public function removeReview(Review $r): static
    {
        if ($this->reviews->removeElement($r)) {
            if ($r->getOffer() === $this) {
                $r->setOffer(null);
            }
        }
        return $this;
    }
}
