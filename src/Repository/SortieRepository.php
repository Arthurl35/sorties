<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
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
     * @throws Exception
     */
    public function findByFilter($filter, $user, $etatPassee): array
    {
       //$requete = $this->createQueryBuilder('s')->innerJoin('sortie_participant on sortie_participant.sortie_id = sortie.id', 'sp');
        $requete = $this->createQueryBuilder('s')->innerJoin(Participant::class, 'p');

         if($filter->getSite() != null) $requete->andWhere('s.site = :val')->setParameter('val', $filter->getSite());
         if($filter->getNom() != null) $requete->andWhere('s.nom LIKE :val')->setParameter('val', '%'.$filter->getNom().'%');
         if($filter->getDateHeureDebut() != null){
            if($filter->getDateHeureFin() == null) $filter->setDateHeureFin(DateTime::createFromFormat('Y-m-d', '2100-01-01'));
                $requete->andWhere('s.dateHeureDebut BETWEEN :val1 AND :val2')->setParameter('val1', $filter->getDateHeureDebut())->setParameter('val2', $filter->getDateHeureFin());
         }
         elseif ($filter->getDateHeureFin() != null){
            $filter->setDateHeureDebut(DateTime::createFromFormat('Y-m-d', '2000-01-01'));
            $requete->andWhere('s.dateHeureDebut BETWEEN :val1 AND :val2')->setParameter('val1', $filter->getDateHeureDebut())->setParameter('val2', $filter->getDateHeureFin());
         }
         if($filter->isSortieOrganisateur()) $requete->andWhere('s.organisateur = :val')->setParameter('val', $user);
         //if($filter->isSortieInscrit()) $requete->addSelect('p')->andWhere('p.id = :val')->setParameter('val', $user->getId());
         //if($filter->isSortiePasInscrit()) $requete->andWhere('p.participants != :val')->setParameter('val', $user);
         if($filter->isSortiePasse()) $requete->andWhere('s.etat = :val')->setParameter('val', $etatPassee);
        return $requete->setMaxResults(1000)->getQuery()->getResult();

        /*$requete = 'select * from sortie inner join sortie_participant on sortie_participant.sortie_id = sortie.id where 1 ';
        $requete2 = "select distinct sortie.id as 'id', sortie.nom as 'nom', sortie.date_heure_debut as 'date_heure_debut', sortie.duree as 'duree', sortie.date_limite_inscription as 'date_limite_inscription', sortie.nb_inscription_max as 'nb_inscription_max', sortie.infos_sortie as 'infos_sortie', sortie.lieu_id as 'lieu_id', sortie.etat_id as 'etat_id', sortie.site_id as 'site_id', sortie.organisateur_id as 'organisateur_id'
                        from sortie inner join sortie_participant on sortie_participant.sortie_id = sortie.id where 1 ";

         if($filter->getSite() != null) $requete+='and where sortie.site_id = '.$filter->getSite()->getId().' ';
         if($filter->getNom() != null) $requete+="and where sortie.nom = '".$filter->getNom()."' ";
         if($filter->getDateHeureDebut() != null){
            if($filter->getDateHeureFin() == null) $filter->setDateHeureFin(new \DateTime());
                     // ajout
         }
         elseif ($filter->getDateHeureFin() != null){
            $filter->setDateHeureDebut(new \DateTime());
                     // ajout
         }
         if($filter->isSortieOrganisateur()) $requete+='and where sortie.organisateur_id = '.$idUser.' ';
         if($filter->isSortieInscrit()) $requete+='and where sortie_participant.participant_id = '.$idUser.' ';
         if($filter->isSortiePasInscrit()) $requete+='and where sortie_participant.participant_id != '.$idUser.' ';
         if($filter->isSortiePasse()) $requete+='and where sortie.participant_id = 5 ';

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->executeQuery($requete2);
        return $stmt->fetchAll();*/

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
            ->getQuery()
            ->getResult()
            ;
    }


}
