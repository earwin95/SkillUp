<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findOneByOfferAndParticipant(Offer $offer, User $participant): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.offer = :offer')
            ->andWhere('c.participant = :participant')
            ->setParameter('offer', $offer)
            ->setParameter('participant', $participant)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Nombre de messages non lus pour un utilisateur (toutes conversations)
     */
    public function countUnreadForUser(User $user): int
    {
        $em = $this->getEntityManager(); // <-- au lieu de $this->_em
        $qb = $em->createQueryBuilder();

        $qb->select('COUNT(m.id)')
           ->from(Message::class, 'm')
           ->join('m.conversation', 'c')
           ->where(
               $qb->expr()->orX(
                   $qb->expr()->andX(
                       'c.owner = :u',
                       'm.author != :u',
                       $qb->expr()->orX('c.ownerLastSeenAt IS NULL', 'm.createdAt > c.ownerLastSeenAt')
                   ),
                   $qb->expr()->andX(
                       'c.participant = :u',
                       'm.author != :u',
                       $qb->expr()->orX('c.participantLastSeenAt IS NULL', 'm.createdAt > c.participantLastSeenAt')
                   )
               )
           )
           ->setParameter('u', $user);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Conversations d’un utilisateur triées par activité récente
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.owner = :u OR c.participant = :u')
            ->setParameter('u', $user)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
