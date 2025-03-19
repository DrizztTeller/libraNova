<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\RentingHistory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<RentingHistory>
 */
class RentingHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RentingHistory::class);
    }

    public function findCurrentRentalsForUser(User $user): array
    {
        $currentDate = new \DateTimeImmutable();

        return $this->createQueryBuilder('rh')
            ->andWhere('rh.user = :user')
            ->andWhere('rh.end > :currentDate OR rh.end IS NULL')
            ->setParameter('user', $user)
            ->setParameter('currentDate', $currentDate)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return RentingHistory[] Returns an array of RentingHistory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RentingHistory
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
