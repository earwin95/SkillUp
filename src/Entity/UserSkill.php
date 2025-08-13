<?php

namespace App\Entity;

use App\Enum\SkillLevel;
use App\Repository\UserSkillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserSkillRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_user_skill_pair', columns: ['user_id', 'skill_id'])]
#[UniqueEntity(fields: ['user', 'skill'], message: 'Cette compétence est déjà associée à cet utilisateur.')]
class UserSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Un UserSkill appartient à UN user (obligatoire)
    #[ORM\ManyToOne(inversedBy: 'userSkills')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?User $user = null;

    // Un UserSkill correspond à UNE skill (obligatoire)
    #[ORM\ManyToOne(inversedBy: 'userSkills')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Skill $skill = null;

    // Niveau avec un enum PHP (BEGINNER / INTERMEDIATE / ADVANCED / EXPERT)
    #[ORM\Column(enumType: SkillLevel::class)]
    private SkillLevel $level = SkillLevel::BEGINNER;

    // Notes/description optionnelle spécifique à ce user+skill
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(?Skill $skill): self
    {
        $this->skill = $skill;
        return $this;
    }

    public function getLevel(): SkillLevel
    {
        return $this->level;
    }

    public function setLevel(SkillLevel $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }
}
