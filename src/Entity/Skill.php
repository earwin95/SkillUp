<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'skillOffered', targetEntity: Offer::class)]
    private Collection $offers;

    #[ORM\OneToMany(mappedBy: 'skillRequested', targetEntity: Offer::class)]
    private Collection $requestedOffers;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'skills')]
    private Collection $users;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
        $this->requestedOffers = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /** ---------- Relation avec Offer (skillOffered) ---------- */

    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): static
    {
        if (!$this->offers->contains($offer)) {
            $this->offers->add($offer);
            $offer->setSkillOffered($this);
        }
        return $this;
    }

    public function removeOffer(Offer $offer): static
    {
        if ($this->offers->removeElement($offer)) {
            if ($offer->getSkillOffered() === $this) {
                $offer->setSkillOffered(null);
            }
        }
        return $this;
    }

    /** ---------- Relation avec Offer (skillRequested) ---------- */

    public function getRequestedOffers(): Collection
    {
        return $this->requestedOffers;
    }

    public function addRequestedOffer(Offer $requestedOffer): static
    {
        if (!$this->requestedOffers->contains($requestedOffer)) {
            $this->requestedOffers->add($requestedOffer);
            $requestedOffer->setSkillRequested($this);
        }
        return $this;
    }

    public function removeRequestedOffer(Offer $requestedOffer): static
    {
        if ($this->requestedOffers->removeElement($requestedOffer)) {
            if ($requestedOffer->getSkillRequested() === $this) {
                $requestedOffer->setSkillRequested(null);
            }
        }
        return $this;
    }

    /** ---------- Relation ManyToMany avec User ---------- */

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addSkill($this);
        }
        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeSkill($this);
        }
        return $this;
    }
}
