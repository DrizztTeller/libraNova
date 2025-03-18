<?php

namespace App\Service;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;

class SearchService
{
  private EntityManagerInterface $entityManager;
  private const ALLOWED_PROPERTIES = ['title', 'author', 'created_at', 'released_at', 'updated_at', 'likes', 'tags'];
  private const ALLOWED_SORT_FIELDS = ['title', 'author', 'created_at', 'released_at', 'updated_at', 'likes'];

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
        $queryBuilder->join('e.tags', 't')
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
    $oneWeekAgo = new DateTimeImmutable('-1 week', new DateTimeZone('Europe/Paris'));

    if (!empty($criteria['created_within_week'])) {
      $queryBuilder->andWhere('e.created_at >= :createdOneWeekAgo')
        ->setParameter('createdOneWeekAgo', $oneWeekAgo);
    }

    if (!empty($criteria['published_within_week'])) {
      $queryBuilder->andWhere('e.released_at >= :publishedOneWeekAgo')
        ->setParameter('publishedOneWeekAgo', $oneWeekAgo);
    }

    if (!empty($criteria['updated_within_week'])) {
      $queryBuilder->andWhere('e.updated_at >= :updatedOneWeekAgo')
        ->setParameter('updatedOneWeekAgo', $oneWeekAgo);
    }
  }

  private function applyTagFilters(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['tags'])) {
      $tagIds = $criteria['tags'] instanceof Collection
        ? array_map(fn($tag) => $tag->getId(), $criteria['tags']->toArray())
        : array_map(fn($tag) => $tag->getId(), (array) $criteria['tags']);

      if (!empty($tagIds)) {
        $queryBuilder->join('e.tags', 'tag')
          ->andWhere('tag.id IN (:tags)')
          ->setParameter('tags', $tagIds);
      }

      if (!empty($criteria['matchType']) && $criteria['matchType'] === 'all') {
        $queryBuilder->groupBy('e.id')
          ->having('COUNT(DISTINCT tag.id) = :tagCount')
          ->setParameter('tagCount', count($tagIds));
      }
    }
  }

  private function applyExcludedTagsFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['excludeTags'])) {
      $excludeTagIds = $criteria['excludeTags'] instanceof Collection
        ? array_map(fn($tag) => $tag->getId(), $criteria['excludeTags']->toArray())
        : array_map(fn($tag) => $tag->getId(), (array) $criteria['excludeTags']);

      if (!empty($excludeTagIds)) {
        $queryBuilder->andWhere($queryBuilder->expr()->not(
          $queryBuilder->expr()->exists(
            $this->entityManager->createQueryBuilder()
              ->select('1')
              ->from('App\Entity\Book', 'b')
              ->join('b.tags', 'et')
              ->where('b.id = e.id')
              ->andWhere('et.id IN (:excludeTags)')
              ->getDQL()
          )
        ))
          ->setParameter('excludeTags', $excludeTagIds);
      }
    }
  }


  private function applyLikesFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (isset($criteria['likes'])) {
      // Si 'likes' est égal à true, on filtre pour les livres ayant plus de 10 likes
      if ($criteria['likes'] === true) {
        $queryBuilder->leftJoin('e.likes', 'u')
          ->groupBy('e.id')
          ->having('COALESCE(COUNT(u.id), 0) >= :likes')
          ->setParameter('likes', 10);
      }
      // Si 'likes' est égal à false, on filtre pour les livres ayant moins de 10 likes
      elseif ($criteria['likes'] === false) {
        $queryBuilder->leftJoin('e.likes', 'u')
          ->groupBy('e.id')
          ->having('COALESCE(COUNT(u.id), 0) < :likes')
          ->setParameter('likes', 10);
      }
      // Si 'likes' est null (Tous), il n'y a pas de filtrage sur les likes
    }
  }


  private function applyPublicationStatusFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (isset($criteria['is_published'])) {
      $queryBuilder->andWhere('e.is_published = :isPublished')
        ->setParameter('isPublished', $criteria['is_published']);
    }
  }

  private function applyAdultFilter(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (isset($criteria['is_for_adult'])) {
      $queryBuilder->andWhere('e.is_for_adult = :isAdult')
        ->setParameter('isAdult', $criteria['is_for_adult']);
    }
  }

  private function applySorting(QueryBuilder $queryBuilder, array $criteria): void
  {
    if (!empty($criteria['orderBy']) && in_array($criteria['orderBy'], self::ALLOWED_SORT_FIELDS, true)) {
      $direction = strtoupper($criteria['orderDirection'] ?? 'DESC');
      $direction = in_array($direction, ['ASC', 'DESC']) ? $direction : 'DESC';

      if ($criteria['orderBy'] === 'likes') {
        $queryBuilder->leftJoin('e.likes', 'l')
          ->groupBy('e.id')
          ->orderBy('COUNT(l.id)', $direction);
      } else {
        $queryBuilder->orderBy('e.' . $criteria['orderBy'], $direction);
      }
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
