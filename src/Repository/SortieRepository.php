<?php

namespace App\Repository;

use App\Entity\Sortie;
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
    public function findByFilter($filter, $idUser): array
    {
   /*     $requete = $this->createQueryBuilder('s')->innerJoin('sortie_participant on sortie_participant.sortie_id = sortie.id', 'sp');

         if($filter->getSite() != null) $requete->andWhere('s.site_id = :val')->setParameter('val', $filter->getSite()->getId());
         if($filter->getNom() != null) $requete->andWhere('s.nom = :val')->setParameter('val', $filter->getNom());
         if($filter->getDateHeureDebut() != null){
            if($filter->getDateHeureFin() == null) $filter->setDateHeureFin(new \DateTime());
                     // ajout
         }
         elseif ($filter->getDateHeureFin() != null){
            $filter->setDateHeureDebut(new \DateTime());
                     // ajout
         }
         ($filter->getSortieOrganisateur()) $requete->andWhere('s.organisateur_id = :val')->setParameter('val', $idUser);
         if($filter->getSortieInscrit()) $requete->andWhere('sp.participant_id = :val')->setParameter('val', $idUser);
         if($filter->getSortiePasInscrit()) $requete->andWhere('sp.participant_id != :val')->setParameter('val', $idUser);
         if($filter->getSortiePasse()) $requete->andWhere('s.etat_id = :val')->setParameter('val', 5);

        return $requete->setMaxResults(100)->getQuery()->getResult();
*/
        $requete = 'select * from sortie inner join sortie_participant on sortie_participant.sortie_id = sortie.id where 1 ';
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
        return $stmt->fetchAll();

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
