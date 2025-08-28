<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'Cette compétence existe déjà.')]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    #[ORM\Column(length: 100, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /** @var Collection<int, UserSkill> */
    #[ORM\OneToMany(mappedBy: 'skill', targetEntity: UserSkill::class, orphanRemoval: false)]
    private Collection $userSkills;

    // 🆕 Ajout des relations avec Offer
    #[ORM\OneToMany(mappedBy: 'skillOffered', targetEntity: Offer::class)]
    private Collection $offers;

    #[ORM\OneToMany(mappedBy: 'skillRequested', targetEntity: Offer::class)]
    private Collection $requestedOffers;

    public function __construct()
    {
        $this->userSkills = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->requestedOffers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->name ?? 'Skill#'.$this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /** @return Collection<int, UserSkill> */
    public function getUserSkills(): Collection
    {
        return $this->userSkills;
    }

    public function addUserSkill(UserSkill $userSkill): self
    {
        if (!$this->userSkills->contains($userSkill)) {
            $this->userSkills->add($userSkill);
            $userSkill->setSkill($this);
        }
        return $this;
    }

    public function removeUserSkill(UserSkill $userSkill): self
    {
        if ($this->userSkills->removeElement($userSkill)) {
            if ($userSkill->getSkill() === $this) {
                $userSkill->setSkill(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Offer> */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): self
    {
        if (!$this->offers->contains($offer)) {
            $this->offers->add($offer);
            $offer->setSkillOffered($this);
        }
        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offers->removeElement($offer)) {
            if ($offer->getSkillOffered() === $this) {
                $offer->setSkillOffered(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Offer> */
    public function getRequestedOffers(): Collection
    {
        return $this->requestedOffers;
    }

    public function addRequestedOffer(Offer $offer): self
    {
        if (!$this->requestedOffers->contains($offer)) {
            $this->requestedOffers->add($offer);
            $offer->setSkillRequested($this);
        }
        return $this;
    }

    public function removeRequestedOffer(Offer $offer): self
    {
        if ($this->requestedOffers->removeElement($offer)) {
            if ($offer->getSkillRequested() === $this) {
                $offer->setSkillRequested(null);
            }
        }
        return $this;
    }
}
