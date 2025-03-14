<?php

namespace App\Repository;

use App\Entity\Tag;
// use Doctrine\DBAL\Types\Types;
use App\Entity\User;
use App\Entity\Novel;
use App\Service\SearchService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
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

    public function findBookmarkedWithFilters(User $user, array $filters, array $sort): array
    {
        $qb = $this->createQueryBuilder('n')
            ->innerJoin('n.tags', 't') // Meilleure performance pour les filtres tags
            ->leftJoin('n.likes', 'u')
            ->addSelect('COUNT(DISTINCT u.id) AS HIDDEN likes_count') // Évite les doublons
            ->andWhere(':user MEMBER OF n.likes')
            ->setParameter('user', $user)
            ->groupBy('n.id');
    
        // Application des filtres avec validation
        $this->applyFilters($qb, $filters);
        
        // Gestion du tri dynamique
        $this->applySorting($qb, $sort);
    
        return $qb->getQuery()->getResult();
    }
    
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        // Filtre de publication
        if (array_key_exists('is_published', $filters)) {
            $qb->andWhere('n.is_published = :isPublished')
               ->setParameter('isPublished', $filters['is_published'], \PDO::PARAM_BOOL);
        }
    
        // Filtre des nouveautés
        if (!empty($filters['newly_available'])) {
            $qb->andWhere('n.updated_at >= :date')
               ->setParameter('date', new \DateTimeImmutable('-7 days'), Types::DATETIME_IMMUTABLE);
        }
    
        // Filtre des tags (optimisé avec IDs)
        if (!empty($filters['tags'])) {
            $tagIds = array_map(fn(Tag $tag) => $tag->getId(), $filters['tags']);
            $qb->andWhere('t.id IN (:tagIds)')
               ->setParameter('tagIds', $tagIds);
        }
    }
    
    private function applySorting(QueryBuilder $qb, array $sort): void
    {
        $sortMap = [
            'author' => 'n.author',
            'popularity' => 'likes_count',
            'released_at' => 'n.released_at',
            'updated_at' => 'n.updated_at',
            'created_at' => 'n.created_at'
        ];
    
        foreach ($sort as $field => $order) {
            $dqlField = $sortMap[$field] ?? "n.$field";
            $qb->addOrderBy($dqlField, strtoupper($order) === 'ASC' ? 'ASC' : 'DESC');
        }
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
    //     $allowedProperties = ['title', 'author', 'created_at', 'released_at', 'likes'];
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
    //         $type = in_array($property, ['created_at', 'released_at']) ? Types::DATETIME_IMMUTABLE : Types::STRING;
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
