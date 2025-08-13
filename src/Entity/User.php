<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', columns: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 100)]
    #[ORM\Column(length: 100)]
    private ?string $username = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\OneToMany(targetEntity: Offer::class, mappedBy: 'owner')]
    private Collection $offers;

    // SUPPRIMÉ: ManyToMany $skills (on passe par UserSkill)
    // #[ORM\ManyToMany(targetEntity: Skill::class, mappedBy: 'users')]
    // private Collection $skills;

    #[ORM\OneToMany(mappedBy: 'requester', targetEntity: ExchangeRequest::class)]
    private Collection $exchangeRequests;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Review::class)]
    private Collection $reviewsWritten;

    #[ORM\OneToMany(mappedBy: 'targetUser', targetEntity: Review::class)]
    private Collection $reviewsReceived;

    /** @var Collection<int, UserSkill> */
    #[ORM\OneToMany(targetEntity: UserSkill::class, mappedBy: 'user')]
    private Collection $userSkills;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
        $this->exchangeRequests = new ArrayCollection();
        $this->reviewsWritten = new ArrayCollection();
        $this->reviewsReceived = new ArrayCollection();
        $this->userSkills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // $this->plainPassword = null; // si tu en utilises un
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    /** @return Collection<int, Offer> */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): static
    {
        if (!$this->offers->contains($offer)) {
            $this->offers->add($offer);
            $offer->setOwner($this);
        }
        return $this;
    }

    public function removeOffer(Offer $offer): static
    {
        if ($this->offers->removeElement($offer)) {
            if ($offer->getOwner() === $this) {
                $offer->setOwner(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, ExchangeRequest> */
    public function getExchangeRequests(): Collection
    {
        return $this->exchangeRequests;
    }

    public function addExchangeRequest(ExchangeRequest $exchangeRequest): static
    {
        if (!$this->exchangeRequests->contains($exchangeRequest)) {
            $this->exchangeRequests->add($exchangeRequest);
            $exchangeRequest->setRequester($this);
        }
        return $this;
    }

    public function removeExchangeRequest(ExchangeRequest $exchangeRequest): static
    {
        if ($this->exchangeRequests->removeElement($exchangeRequest)) {
            if ($exchangeRequest->getRequester() === $this) {
                $exchangeRequest->setRequester(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Review> */
    public function getReviewsWritten(): Collection
    {
        return $this->reviewsWritten;
    }

    public function addReviewWritten(Review $review): static
    {
        if (!$this->reviewsWritten->contains($review)) {
            $this->reviewsWritten->add($review);
            $review->setAuthor($this);
        }
        return $this;
    }

    public function removeReviewWritten(Review $review): static
    {
        if ($this->reviewsWritten->removeElement($review)) {
            if ($review->getAuthor() === $this) {
                $review->setAuthor(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Review> */
    public function getReviewsReceived(): Collection
    {
        return $this->reviewsReceived;
    }

    public function addReviewReceived(Review $review): static
    {
        if (!$this->reviewsReceived->contains($review)) {
            $this->reviewsReceived->add($review);
            $review->setTargetUser($this);
        }
        return $this;
    }

    public function removeReviewReceived(Review $review): static
    {
        if ($this->reviewsReceived->removeElement($review)) {
            if ($review->getTargetUser() === $this) {
                $review->setTargetUser(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, UserSkill> */
    public function getUserSkills(): Collection
    {
        return $this->userSkills;
    }

    public function addUserSkill(UserSkill $userSkill): static
    {
        if (!$this->userSkills->contains($userSkill)) {
            $this->userSkills->add($userSkill);
            $userSkill->setUser($this);
        }
        return $this;
    }

    public function removeUserSkill(UserSkill $userSkill): static
    {
        if ($this->userSkills->removeElement($userSkill)) {
            if ($userSkill->getUser() === $this) {
                $userSkill->setUser(null);
            }
        }
        return $this;
    }
}
