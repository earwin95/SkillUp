<?php

// src/Repository/ExchangeRequestRepository.php

namespace App\Repository;

use App\Entity\Offer;
use App\Entity\ExchangeRequest;
use App\Enum\ExchangeRequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExchangeRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRequest::class);
    }

    /**
     * Retourne les demandes d’échange en attente pour une offre donnée.
     *
     * @param Offer $offer
     * @return ExchangeRequest[]
     */
    public function findPendingForOffer(Offer $offer): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.offer = :offer')
            ->andWhere('e.status = :status')
            ->setParameter('offer', $offer)
            ->setParameter('status', ExchangeRequestStatus::PENDING->value) // <= version correcte
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
