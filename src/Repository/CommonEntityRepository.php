<?php

namespace App\Repository;

use App\Entity\CommonEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommonEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommonEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommonEntity[]    findAll()
 * @method CommonEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommonEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommonEntity::class);
    }

    // /**
    //  * @return CommonEntity[] Returns an array of CommonEntity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CommonEntity
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
