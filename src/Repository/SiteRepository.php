<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Site>
 *
 * @method Site|null find($id, $lockMode = null, $lockVersion = null)
 * @method Site|null findOneBy(array $criteria, array $orderBy = null)
 * @method Site[]    findAll()
 * @method Site[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }


    public function insert(Site $pointVente): void
    {
        // Construction de la requête d'insertion
        $query = "
            INSERT INTO site (libelle, adresse, coord) 
            VALUES (:libelle, :adresse, ST_GeomFromText(CONCAT('POINT(', :latitude, ' ', :longitude, ')'), 4326))
        ";

        // Exécution de la requête avec executeStatement pour obtenir le nombre de lignes affectées
        $result = $this->getEntityManager()->getConnection()->executeStatement($query, [
            'libelle' => $pointVente->getLibelle(),
            'adresse' => $pointVente->getAdresse(),
            'latitude' => $pointVente->getLatitude(),
            'longitude' => $pointVente->getLongitude(),
        ]);
    }

    public function getZoneOfSite($siteId)
    {
        $entityManager = $this->getEntityManager();

         // Créez un ResultSetMapping
         $rsm = new ResultSetMappingBuilder($this->getEntityManager());

         // Ajoutez le mapping pour l'entité ZoneVente
         $rsm->addRootEntityFromClassMetadata('App\Entity\Zone', 'z');

        $nativeQuery = $entityManager->createNativeQuery('
            SELECT z.*
            FROM site s
            JOIN zone z ON ST_Intersects(s.coord, z.coord)
            WHERE s.id = :siteId
        ',$rsm);
        

        $nativeQuery->setParameter('siteId', $siteId);
        
        $result = $nativeQuery->getResult();

        return $result;
    }

//    /**
//     * @return Site[] Returns an array of Site objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Site
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}