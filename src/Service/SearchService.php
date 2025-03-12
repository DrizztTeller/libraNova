<?php

namespace App\Service;

use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class SearchService
{
  private EntityManagerInterface $entityManager;
  private const ALLOWED_PROPERTIES = ['title', 'author', 'created_at', 'published_at', 'likes', 'tags'];

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

  public function search(QueryBuilder $queryBuilder, array $criteria): array
  {
    $this->applyPropertyFilter($queryBuilder, $criteria);
    $this->applyTitleFilter($queryBuilder, $criteria);
    $this->applyAuthorFilter($queryBuilder, $criteria);
    $this->applyDateFilters($queryBuilder, $criteria);
    $this->applyTagFilters($queryBuilder, $criteria);
    $this->applyExcludedTagsFilter($queryBuilder, $criteria);
    $this->applyLikesFilter($queryBuilder, $criteria);
    $this->applyPublicationStatusFilter($queryBuilder, $criteria);
    $this->applyAdultFilter($queryBuilder, $criteria);
    $this->applySorting($queryBuilder, $criteria);
    $this->applyLimit($queryBuilder, $criteria);

    return $queryBuilder->getQuery()->getResult();
  }

  private function applyPropertyFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['property']) && in_array($criteria['property'], self::ALLOWED_PROPERTIES, true)) {
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
  }

  private function applyTitleFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['title'])) {
      $queryBuilder->andWhere('e.title LIKE :title')
        ->setParameter('title', '%' . $criteria['title'] . '%');
    }
  }

  private function applyAuthorFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['author'])) {
      $queryBuilder->andWhere('e.author LIKE :author')
        ->setParameter('author', '%' . $criteria['author'] . '%');
    }
  }

  private function applyDateFilters(QueryBuilder $queryBuilder, array $criteria): void
  {
    $oneWeekAgo = new DateTimeImmutable('-1 week');

    if (!empty($criteria['created_within_week'])) {
      $queryBuilder->andWhere('e.created_at >= :createdOneWeekAgo')
        ->setParameter('createdOneWeekAgo', $oneWeekAgo);
    }

    if (!empty($criteria['published_within_week'])) {
      $queryBuilder->andWhere('e.published_at >= :publishedOneWeekAgo')
        ->setParameter('publishedOneWeekAgo', $oneWeekAgo);
    }
  }

  private function applyTagFilters(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['tags'])) {
      $queryBuilder->join('e.tag', 't')
        ->andWhere('t.id IN (:tags)')
        ->setParameter('tags', $criteria['tags']);

      if (!empty($criteria['matchType']) && $criteria['matchType'] === 'all') {
        $queryBuilder->groupBy('e.id')
          ->having('COUNT(DISTINCT t.id) = :tagCount')
          ->setParameter('tagCount', count($criteria['tags']));
      }
    }
  }

  private function applyExcludedTagsFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
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
  }

  private function applyLikesFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['likes']) && is_numeric($criteria['likes'])) {
      $queryBuilder->leftJoin('e.likes', 'l')
        ->groupBy('e.id')
        ->having('COUNT(l.id) >= :likes')
        ->setParameter('likes', $criteria['likes']);
    }
  }

  private function applyAdultFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (isset($criteria['is_for_adult'])) {
      $queryBuilder->andWhere('e.is_for_adult = :isAdult')
        ->setParameter('isAdult', $criteria['is_for_adult']);
    }
  }

  private function applyPublicationStatusFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (isset($criteria['is_published'])) {
      $queryBuilder->andWhere('e.is_published = :isPublished')
        ->setParameter('isPublished', $criteria['is_published']);
    }
  }

  private function applySorting(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['orderBy']) && !empty($criteria['orderDirection'])) {
      $queryBuilder->orderBy('e.' . $criteria['orderBy'], $criteria['orderDirection']);
    } else {
      $queryBuilder->orderBy('e.created_at', 'DESC');
    }
  }

  private function applyLimit(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['limit']) && is_numeric($criteria['limit'])) {
      $queryBuilder->setMaxResults($criteria['limit']);
    }
  }
}
