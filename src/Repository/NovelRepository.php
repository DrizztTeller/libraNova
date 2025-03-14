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

    
    public function findBySearchCriteria(array $criteria = []): array
{
    $qb = $this->createQueryBuilder('n');

    // Filtrage par titre
    if (!empty($criteria['title'])) {
        $qb->andWhere('n.title LIKE :title')
           ->setParameter('title', '%' . $criteria['title'] . '%');
    }
    
    // Filtrage par auteur
    if (!empty($criteria['author'])) {
        $qb->andWhere('n.author LIKE :author')
           ->setParameter('author', '%' . $criteria['author'] . '%');
    }
    
    // Filtrage par tags
    if (!empty($criteria['tags'])) {
        $qb->join('n.tags', 't')
           ->andWhere('t IN (:tags)')
           ->setParameter('tags', $criteria['tags']);
    }
    
    // Filtrage par contenu adulte
    if (isset($criteria['isForAdult'])) {
        $qb->andWhere('n.is_for_adult = :isForAdult')
           ->setParameter('isForAdult', $criteria['isForAdult']);
    }
    
    // Tri
    $orderBy = !empty($criteria['orderBy']) ? $criteria['orderBy'] : 'created_at';
    $orderDirection = !empty($criteria['orderDirection']) ? $criteria['orderDirection'] : 'DESC';
    
    // Convertir les noms de colonnes pour qu'ils correspondent à votre entité
    $orderByMap = [
        'created_at' => 'n.created_at',
        'released_at' => 'n.released_at',
        'likes' => 'SIZE(n.likes)', // Pour compter les likes
        'title' => 'n.title'
    ];
    
    $orderByField = isset($orderByMap[$orderBy]) ? $orderByMap[$orderBy] : 'n.created_at';
    
    $qb->orderBy($orderByField, $orderDirection);
    
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
