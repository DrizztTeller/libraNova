<?php

namespace App\Repository;

use App\Entity\User;
// use Doctrine\DBAL\Types\Types;
use App\Entity\Novel;
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

public function findBookmarkedWithFilters(User $user, array $filters, array $sort): array
{
    $qb = $this->createQueryBuilder('n')
        ->leftJoin('n.tags', 't')
        ->leftJoin('n.likes', 'u') 
        ->addSelect('COUNT(u.id) AS HIDDEN likes_count') 
        ->andWhere(':user MEMBER OF n.likes')
        ->setParameter('user', $user)
        ->groupBy('n.id');

    // Filtres
    if (array_key_exists('is_published', $filters)) {
        $qb->andWhere('n.is_published = :isPublished')
           ->setParameter('isPublished', $filters['is_published']);
    }

    if (isset($filters['newly_available']) && $filters['newly_available']) {
        $date = new \DateTime('-7 days');
        $qb->andWhere('n.updated_at >= :date') 
           ->setParameter('date', $date);
    }

    if (!empty($filters['tags'])) {
        $qb->andWhere('t IN (:tags)')
           ->setParameter('tags', $filters['tags']);
    }

    // Tri
    foreach ($sort as $field => $order) {
        switch ($field) {
            case 'author':
                $qb->addOrderBy('n.author', $order); 
                break;
                
            case 'popularity':
                $qb->addOrderBy('likes_count', $order); 
                break;
                
            case 'released_at':
                $qb->addOrderBy('n.released_at', $order); 
                break;
                
            case 'updated_at':
                $qb->addOrderBy('n.updated_at', $order);
                break;
                
            case 'created_at':
                $qb->addOrderBy('n.created_at', $order);
                break;
                
            default:
                $qb->addOrderBy("n.$field", $order);
        }
    }

    return $qb->getQuery()->getResult();
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
