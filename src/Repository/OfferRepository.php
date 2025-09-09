<?php

namespace App\Repository;

use App\Entity\Offer;
use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offer>
 */
class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    /**
     * Recherche paginée par compétences (offerte/demandée) et mot-clé (titre/description/nom de skill).
     *
     * @return array{items: array<Offer>, total:int, pages:int}
     */
    public function findByFiltersPaginated(
        ?Skill $offered,
        ?Skill $requested,
        ?string $q,
        int $page = 1,
        int $limit = 10
    ): array {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.owner', 'owner')->addSelect('owner')
            ->leftJoin('o.skillOffered', 'skillOffered')->addSelect('skillOffered')
            ->leftJoin('o.skillRequested', 'skillRequested')->addSelect('skillRequested');

        if ($offered) {
            $qb->andWhere('o.skillOffered = :offered')
               ->setParameter('offered', $offered);
        }

        if ($requested) {
            $qb->andWhere('o.skillRequested = :requested')
               ->setParameter('requested', $requested);
        }

        if ($q !== null && trim($q) !== '') {
            $qNorm = mb_strtolower(trim($q));
            $qb->andWhere(
                'LOWER(o.title) LIKE :q
                 OR LOWER(o.description) LIKE :q
                 OR LOWER(skillOffered.name) LIKE :q
                 OR LOWER(skillRequested.name) LIKE :q'
            )->setParameter('q', '%'.$qNorm.'%');
        }

        // tri par id meme si createdAt n'existe pas
        $meta = $this->getEntityManager()->getClassMetadata(Offer::class);
        $sortField = $meta->hasField('createdAt') ? 'o.createdAt' : 'o.id';
        $qb->orderBy($sortField, 'DESC');

        $page  = max(1, $page);
        $limit = max(1, $limit);

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        // Paginator sur Query
        $paginator = new Paginator($qb->getQuery(), true);
        $total = count($paginator);
        $pages = (int) ceil($total / $limit);

        $items = [];
        foreach ($paginator as $offer) {
            $items[] = $offer;
        }

        return [
            'items' => $items,
            'total' => $total,
            'pages' => max(1, $pages),
        ];
    }

    /**
     * Recherche simple (sans pagination) par terme de compétence.
     * Match sur le nom de la compétence OFFERTE ou DEMANDÉE.
     *
     * @return Offer[]
     */
    public function findBySkillTerm(?string $term): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.owner', 'owner')->addSelect('owner')
            ->leftJoin('o.skillOffered', 'skillOffered')->addSelect('skillOffered')
            ->leftJoin('o.skillRequested', 'skillRequested')->addSelect('skillRequested');

        if ($term !== null && trim($term) !== '') {
            $t = mb_strtolower(trim($term));
            $qb->andWhere('LOWER(skillOffered.name) LIKE :t OR LOWER(skillRequested.name) LIKE :t')
               ->setParameter('t', '%'.$t.'%');
        }

        $meta = $this->getEntityManager()->getClassMetadata(Offer::class);
        $sortField = $meta->hasField('createdAt') ? 'o.createdAt' : 'o.id';

        return $qb->orderBy($sortField, 'DESC')
                  ->getQuery()
                  ->getResult();
    }
}
