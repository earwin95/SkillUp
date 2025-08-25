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
     * @param Offer $offer
     * @return ExchangeRequest[]
     */
    public function findPendingForOffer(Offer $offer): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.offer = :offer')
            ->andWhere('e.status = :status')
            ->setParameter('offer', $offer)
            ->setParameter('status', ExchangeRequestStatus::PENDING)

            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
