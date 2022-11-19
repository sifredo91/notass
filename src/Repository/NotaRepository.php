<?php

namespace App\Repository;

use App\Entity\Nota;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Nota>
 *
 * @method Nota|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nota|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nota[]    findAll()
 * @method Nota[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nota::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Nota $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Nota $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Nota[] Returns an array of Nota objects
    //  */

    public function findNotasByUser(User $user)
    {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.user','u')
            ->andWhere('u= :u')
            ->andWhere('n.iseliminada= :iseliminada')
            ->setParameter('u', $user)
            ->setParameter('iseliminada', false)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNotasEliminadas(User $user)
    {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.user','u')
            ->andWhere('u= :u')
            ->andWhere('n.iseliminada= :iseliminada')
            ->setParameter('u', $user)
            ->setParameter('iseliminada', true)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findNotasPublicasDeOtrosUsuarios(User $user)
    {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.user','u')
            ->andWhere('u<> :u')
            ->andWhere('n.iseliminada= :iseliminada')
            ->setParameter('u', $user)
            ->setParameter('iseliminada', false)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findPublicas(User $user)
    {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.user','u')
            ->andWhere('n.publica= :p')
            ->andWhere('u<> :user')
            ->setParameter('p', true)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }



    /*
    public function findOneBySomeField($value): ?Nota
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
