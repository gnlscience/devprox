<?php

namespace App\Repository;

use App\Entity\CsvImport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CsvImport|null find($id, $lockMode = null, $lockVersion = null)
 * @method CsvImport|null findOneBy(array $criteria, array $orderBy = null)
 * @method CsvImport[]    findAll()
 * @method CsvImport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CsvImportRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CsvImport::class);
    }

    // /**
    //  * @return CsvImport[] Returns an array of CsvImport objects
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
    public function findOneBySomeField($value): ?CsvImport
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
