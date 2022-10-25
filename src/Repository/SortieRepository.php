<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findBySite(String $siteChoix)
    {
        return $this->createQueryBuilder('s')
            ->join('s.site','site')
            ->andWhere('site.nom = :siteChoix')
            ->setParameter('siteChoix', $siteChoix)
            ->getQuery()
            ->getResult()
            ;

    }

    public function findByNom($nomSortie)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.nom LIKE :nomSortie')
            ->setParameter('nomSortie', '%'.$nomSortie.'%')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByDate($dateDSortie, $dateFSortie)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.dateHeureDebut BETWEEN :debut AND :fin' )
            ->setParameter('debut', $dateDSortie->format('Y-m-d'))
            ->setParameter('fin', $dateFSortie->format('Y-m-d'))
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByInscrit($sortie_inscrit)
    {
        return $this->createQueryBuilder('s')
            ->join('s.participant','participant')
            ->join('participant.sorties_inscrits','inscrit')
            ->andWhere('inscrit.sorties_inscrits = :sortie_inscrit')
            ->setParameter('sortie_inscrit', $sortie_inscrit)
            ->getQuery()
            ->getResult()
            ;
    }


}
