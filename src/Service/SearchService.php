<?php

namespace App\Service;

use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class SearchService
{
  private EntityManagerInterface $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

  public function search(QueryBuilder $queryBuilder, array $criteria): array
  {
    // Liste des propriétés autorisées
    $allowedProperties = ['title', 'author', 'created_at', 'published_at', 'likes', 'tags'];

    if (!empty($criteria['property']) && in_array($criteria['property'], $allowedProperties)) {
      // Cas particulier pour les tags (ManyToMany)
      if ($criteria['property'] === 'tags' && !empty($criteria['filter'])) {
        $queryBuilder->join('e.tag', 't')
          ->andWhere('t.id IN (:tags)')
          ->setParameter('tags', $criteria['filter']);
      } else {
        $queryBuilder->andWhere("e.{$criteria['property']} < :filter")
          ->setParameter('filter', $criteria['filter'])
          ->orderBy("e.{$criteria['property']}", 'DESC');
      }
    }

    // Recherche par titre
    if (!empty($criteria['title'])) {
      $queryBuilder->andWhere('e.title LIKE :title')
        ->setParameter('title', '%' . $criteria['title'] . '%');
    }

    // Recherche par auteur
    if (!empty($criteria['author'])) {
      $queryBuilder->andWhere('e.author LIKE :author')
        ->setParameter('author', '%' . $criteria['author'] . '%');
    }

    // Filtrer par date de création (moins d'une semaine)
    if (!empty($criteria['created_within_week']) && $criteria['created_within_week'] === true) {
      $oneWeekAgo = new DateTimeImmutable('-1 week');
      $queryBuilder->andWhere('e.created_at >= :oneWeekAgo')
        ->setParameter('oneWeekAgo', $oneWeekAgo);
    }

    // Filtrer par date de publication (moins d'une semaine)
    if (!empty($criteria['published_within_week']) && $criteria['published_within_week'] === true) {
      $oneWeekAgo = new DateTimeImmutable('-1 week');
      $queryBuilder->andWhere('e.published_at >= :oneWeekAgo')
        ->setParameter('oneWeekAgo', $oneWeekAgo);
    }

    // Filtrer par tags
    if (!empty($criteria['tags'])) {
      $queryBuilder->join('e.tag', 't')
        ->andWhere('t.id IN (:tags)')
        ->setParameter('tags', $criteria['tags']);

      if (!empty($criteria['matchType']) && $criteria['matchType'] === 'all') {
        $queryBuilder->groupBy('e.id')
          ->having('COUNT(t.id) = :tagCount')
          ->setParameter('tagCount', count($criteria['tags']));
      }
    }

    // Exclure certains tags
    if (!empty($criteria['excludeTags'])) {
      $rootEntity = $queryBuilder->getRootEntities()[0];

      $subQuery = $this->entityManager->createQueryBuilder()
        ->select('sub.id')
        ->from($rootEntity, 'sub')
        ->leftJoin('sub.tag', 'st')
        ->where('st.id IN (:excludeTags)');

      $queryBuilder->andWhere($queryBuilder->expr()->notIn('e.id', $subQuery->getDQL()))
        ->setParameter('excludeTags', $criteria['excludeTags']);
    }

    // Filtrer les livres populaires par nombre de likes
    if (!empty($criteria['likes']) && is_numeric($criteria['likes'])) {
      $queryBuilder->leftJoin('e.likes', 'l')
        ->groupBy('e.id')
        ->having('COUNT(l.id) >= :likes')
        ->setParameter('likes', $criteria['likes']);
    }

    // Filtrer par état de publication
    if (!empty($criteria['is_published'])) {
      $queryBuilder->andWhere('e.is_published IN (:statuses)')
        ->setParameter('statuses', $criteria['is_published']);
    }

    // Trier les résultats
    if (!empty($criteria['orderBy']) && !empty($criteria['orderDirection'])) {
      $queryBuilder->orderBy('e.' . $criteria['orderBy'], $criteria['orderDirection']);
    } else {
      $queryBuilder->orderBy('e.created_at', 'DESC');
    }

    return $queryBuilder->getQuery()->getResult();
  }
}
