<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $username = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\OneToMany(targetEntity: Offer::class, mappedBy: 'owner')]
    private Collection $offers;

    #[ORM\ManyToMany(targetEntity: Skill::class, mappedBy: 'users')]
    private Collection $skills;

    #[ORM\OneToMany(mappedBy: 'requester', targetEntity: ExchangeRequest::class)]
    private Collection $exchangeRequests;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Review::class)]
    private Collection $reviewsWritten;

    #[ORM\OneToMany(mappedBy: 'targetUser', targetEntity: Review::class)]
    private Collection $reviewsReceived;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->exchangeRequests = new ArrayCollection();
        $this->reviewsWritten = new ArrayCollection();
        $this->reviewsReceived = new ArrayCollection();
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

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
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

    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->addUser($this);
        }
        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        if ($this->skills->removeElement($skill)) {
            $skill->removeUser($this);
        }
        return $this;
    }

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
}
