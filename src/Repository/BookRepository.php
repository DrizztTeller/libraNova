<?php

namespace App\Repository;

use App\Entity\Tag;
// use Doctrine\DBAL\Types\Types;
use App\Entity\User;
use App\Entity\Book;
use App\Service\SearchService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    private $searchService;

    public function __construct(ManagerRegistry $registry, SearchService $searchService)
    {
        parent::__construct($registry, Book::class);
        $this->searchService = $searchService;
    }

    public function searchBooks(array $criteria)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $this->searchService->search($queryBuilder, $criteria);

        // Voir la requête SQL avant exécution
        // dump($queryBuilder->getQuery()->getSQL()); // Affiche la requête SQL
        // dump($queryBuilder->getParameters()); // Affiche les valeurs des paramètres

        return $queryBuilder->getQuery()->getResult();
    }

    public function findBookmarkedWithFilters(User $user, array $filters, array $sort): array
    {
        $qb = $this->createQueryBuilder('b')
            ->innerJoin('b.tags', 't') // Meilleure performance pour les filtres tags
            ->leftJoin('b.likes', 'u')
            ->addSelect('COUNT(DISTINCT u.id) AS HIDDEN likes_count') // Évite les doublons
            ->andWhere(':user MEMBER OF b.likes')
            ->setParameter('user', $user)
            ->groupBy('b.id');

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
            $qb->andWhere('b.is_published = :isPublished')
                ->setParameter('isPublished', $filters['is_published'], \PDO::PARAM_BOOL);
        }

        // Filtre des nouveautés
        if (!empty($filters['newly_available'])) {
            $qb->andWhere('b.updated_at >= :date')
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
            'author' => 'b.author',
            'popularity' => 'likes_count',
            'released_at' => 'b.released_at',
            'updated_at' => 'b.updated_at',
            'created_at' => 'b.created_at'
        ];

        foreach ($sort as $field => $order) {
            $dqlField = $sortMap[$field] ?? "b.$field";
            $qb->addOrderBy($dqlField, strtoupper($order) === 'ASC' ? 'ASC' : 'DESC');
        }
    }



    //    /**
    //     * @return Book[] Returns an array of Book objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    // public function findByProperty(string $property, string $operator, mixed $filter): array
    // {
    //     $allowedProperties = ['title', 'author', 'created_at', 'released_at', 'likes'];
    //  if (!in_array($property, $allowedProperties)) {
    //     throw new \InvalidArgumentException("Propriété non autorisée.");
    //  }

    //     $allowedOperators = ['=', '!=', '<', '>', '<=', '>='];
    //     if (!in_array($operator, $allowedOperators)) {
    //         throw new \InvalidArgumentException("Opérateur non autorisé.");
    //     }

    //     $qb = $this->createQueryBuilder('n');

    //     if ($property === 'likes') {
    //         // Gestion du COUNT(likes)
    //         $qb->leftJoin('b.likes', 'u')
    //             ->groupBy('b.id')
    //             ->having("COUNT(u.id) $operator :filter")
    //             ->setParameter('filter', $filter);
    //     } else {
    //         // Gestion des autres propriétés
    //         $type = in_array($property, ['created_at', 'released_at']) ? Types::DATETIME_IMMUTABLE : Types::STRING;
    //         $qb->where("b.$property $operator :filter")
    //             ->setParameter('filter', $filter, $type);
    //     }

    //     return $qb->orderBy("b.$property", 'DESC')
    //         ->getQuery()
    //         ->getResult();
    // }

    //    public function findOneBySomeField($value): ?Book
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
