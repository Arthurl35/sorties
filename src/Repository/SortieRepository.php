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

    public function updateEtatSortie(EtatRepository $etatRepository){
        $sorties = $this->findAll();

        //1 Créée
        //2 Ouverte
        //3 Clôturée
        //4 Activité
        //5 Passé
        //6 Annulée
        foreach ($sorties as $sortie){
            switch ($sortie->getEtat()->getId()){
                case 1:
                    $sortie->setEtat($etatRepository->find(2));
                    break;
                case 2:
                    if($sortie->getDateLimiteInscription() < new \DateTime()) $sortie->setEtat($etatRepository->find(3));
                    break;
                case 3:
                    if($sortie->getDateHeureDebut() < new \DateTime()) $sortie->setEtat($etatRepository->find(4));
                    break;
                case 4:
                    $timeStampFin = strtotime($sortie->getDateHeureDebut()->format('d-m-Y h:m:s'))+$sortie->getDuree();
                    $timeStampNow = strtotime((new \DateTime())->format('d-m-Y h:m:s'));

                    if($timeStampNow > $timeStampFin) $sortie->setEtat($etatRepository->find(5));
                    break;
            }
        }
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
}
