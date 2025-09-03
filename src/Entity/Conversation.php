<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\Table(name: 'conversation')]
#[ORM\UniqueConstraint(name: 'uniq_conversation_offer_participant', columns: ['offer_id', 'participant_id'])]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // L'offre concernée
    #[ORM\ManyToOne(targetEntity: Offer::class, inversedBy: 'conversations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Offer $offer = null;

    // Le propriétaire de l'offre (toujours offer->owner)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $owner = null;

    // L'utilisateur qui vient discuter de l'offre (visiteur)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $participant = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: Message::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    // Dernière fois que chacun a vu la conv (pour non-lus)
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $ownerLastSeenAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $participantLastSeenAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->messages = new ArrayCollection();
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markSeenBy(User $user): void
    {
        $now = new \DateTimeImmutable();
        if ($user === $this->owner) {
            $this->ownerLastSeenAt = $now;
        } elseif ($user === $this->participant) {
            $this->participantLastSeenAt = $now;
        }
    }

    public function getLastSeenFor(User $user): ?\DateTimeImmutable
    {
        if ($user === $this->owner) return $this->ownerLastSeenAt;
        if ($user === $this->participant) return $this->participantLastSeenAt;
        return null;
    }

    public function getId(): ?int { return $this->id; }

    public function getOffer(): ?Offer { return $this->offer; }
    public function setOffer(Offer $offer): self { $this->offer = $offer; return $this; }

    public function getOwner(): ?User { return $this->owner; }
    public function setOwner(User $owner): self { $this->owner = $owner; return $this; }

    public function getParticipant(): ?User { return $this->participant; }
    public function setParticipant(User $participant): self { $this->participant = $participant; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function getOwnerLastSeenAt(): ?\DateTimeImmutable { return $this->ownerLastSeenAt; }
    public function getParticipantLastSeenAt(): ?\DateTimeImmutable { return $this->participantLastSeenAt; }

    /** @return Collection<int, Message> */
    public function getMessages(): Collection { return $this->messages; }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
            $this->touch();
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
            $this->touch();
        }
        return $this;
    }

    public function isParticipant(User $user): bool
    {
        return $user === $this->owner || $user === $this->participant;
    }
}
