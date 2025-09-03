<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /** @return Message[] */
    public function findAfterId(Conversation $conv, ?int $afterId): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.conversation = :c')
            ->setParameter('c', $conv)
            ->orderBy('m.createdAt', 'ASC')
            ->addOrderBy('m.id', 'ASC');

        if ($afterId) {
            $qb->andWhere('m.id > :afterId')->setParameter('afterId', $afterId);
        }

        return $qb->getQuery()->getResult();
    }
}
