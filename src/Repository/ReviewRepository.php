<?php
// src/Repository/ReviewRepository.php
namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function getAverageRatingForUser(int $userId): ?float
    {
        $qb = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->andWhere('r.subjectUser = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()->getSingleScalarResult();

        return $qb !== null ? (float) $qb : null;
    }
}
