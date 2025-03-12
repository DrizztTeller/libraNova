<?php

namespace App\Repository;

use App\Entity\Novel;
// use Doctrine\DBAL\Types\Types;
use App\Service\SearchService;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Novel>
 */
class NovelRepository extends ServiceEntityRepository
{
    private $searchService;

    public function __construct(ManagerRegistry $registry, SearchService $searchService)
    {
        parent::__construct($registry, Novel::class);
        $this->searchService = $searchService;
    }

    public function searchNovels(array $criteria)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        return $this->searchService->search($queryBuilder, $criteria);
    }

    //    /**
    //     * @return Novel[] Returns an array of Novel objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    // public function findByProperty(string $property, string $operator, mixed $filter): array
    // {
    //     $allowedProperties = ['title', 'author', 'created_at', 'published_at', 'likes'];
    //     if (!in_array($property, $allowedProperties)) {
    //         throw new \InvalidArgumentException("Propriété non autorisée.");
    //     }
    
    //     $allowedOperators = ['=', '!=', '<', '>', '<=', '>='];
    //     if (!in_array($operator, $allowedOperators)) {
    //         throw new \InvalidArgumentException("Opérateur non autorisé.");
    //     }
    
    //     $qb = $this->createQueryBuilder('n');
    
    //     if ($property === 'likes') {
    //         // Gestion du COUNT(likes)
    //         $qb->leftJoin('n.likes', 'u')
    //             ->groupBy('n.id')
    //             ->having("COUNT(u.id) $operator :filter")
    //             ->setParameter('filter', $filter);
    //     } else {
    //         // Gestion des autres propriétés
    //         $type = in_array($property, ['created_at', 'published_at']) ? Types::DATETIME_IMMUTABLE : Types::STRING;
    //         $qb->where("n.$property $operator :filter")
    //             ->setParameter('filter', $filter, $type);
    //     }
    
    //     return $qb->orderBy("n.$property", 'DESC')
    //         ->getQuery()
    //         ->getResult();
    // }

    //    public function findOneBySomeField($value): ?Novel
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
