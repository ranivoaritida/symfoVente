<?php

namespace App\Controller;

use App\Util\Util;
use App\Entity\Site;
use App\Entity\Zone;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SiteController extends AbstractController
{
    #[Route('/site', name: 'app_site')]
    public function index(): Response
    {
        return $this->render('site/index.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }

    #[Route('/site/test', name: 'app_site_test')]
    public function test(): Response
    {
        return $this->render('site/test.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }
    
    #[Route('/site/insert', name: 'site_insert', methods: ['POST'])]
    public function insertSite(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupéreration des données du formulaire
        $libelle = $request->request->get('libelle');
        $adresse = $request->request->get('adresse');
        $coord = $request->request->get('coord');
        
        // Convertir les coordonnées en tableau [latitude, longitude]
        $coordArray = explode(',', str_replace(['LatLng(', ')'], '', $coord));
        $latitude = floatval($coordArray[0]);
        $longitude = floatval($coordArray[1]);

        // Créer une nouvelle instance de Site et définir ses propriétés
        $site = new Site();
        $site->setLibelle($libelle);
        $site->setAdresse($adresse);
        $site->setCoord(['latitude' => $latitude, 'longitude' => $longitude]);

        $entityManager->getRepository(Site::class)->insert($site);


        // Rediriger vers
        return $this->redirectToRoute('app_site');
    }

    #[Route('/site/liste', name: 'site_liste')]
    public function site(Request $request,EntityManagerInterface $entityManager): Response
    {
        $rsite =  $entityManager->getRepository(Site::class);
        $rzone = $entityManager->getRepository(Zone::class);
        $sites = $rsite->findAll();
        $zones = $rzone->findAll();
        $zone=(Util::toJson($zones));
        $site = (Util::toJson($sites));
       // dump($site);die();
        return $this->render('site/site.html.twig', [
            'sites' => $site,
            'zone' => $zone,
        ]);
    }

    //prendre les zones d'une site 
    #[Route('site/liste/detailSite/{siteId}', name : 'site_liste_detail')]
    public function siteDetail($siteId,EntityManagerInterface $entityManager): Response
    {
        $rsite = $entityManager->getRepository(Site::class);
        //$siteByZone = $rzone->getSitesInZone($zoneId);
        $zoneOfSite = $rsite->getZoneOfSite($siteId);
        //dump($zoneOfSite);die();
        $ato = Util::toJson($zoneOfSite);

        return new JsonResponse($ato);
    }

    //prendre les sites d'une zone 
    #[Route('site/liste/detailZone/{zoneId}', name : 'site_liste_zone_detail')]
    public function zoneDetail($zoneId,EntityManagerInterface $entityManager): Response
    {
        $rzone = $entityManager->getRepository(Zone::class);
        //$siteByZone = $rzone->getSitesInZone($zoneId);
        $sitesInZone = $rzone->getSitesInZone($zoneId);
        //dump($zoneOfSite);die();
        $ato = Util::toJson($sitesInZone);

        return new JsonResponse($ato);
    }



    ///ZONE Controller part
    #[Route('/site/zone/new', name: 'new_zone')]
    public function newZV(EntityManagerInterface $entityManager): Response
    {
        $rzone =  $entityManager->getRepository(Zone::class);
        $zone = $rzone->findAll();
        dump($zone);
        return $this->render('site/new-zone.html.twig', [
            'controller_name' => 'LocationController',
            'zoneventes' => $zone,
            'message' => null,
        ]);
    }

    ///Venant da la form de newZone
    #[Route('/site/zone/insert', name: 'insert_zv', methods: ['POST'])]
    public function traitementbu(Request $request, EntityManagerInterface $em): Response
    {
        $libelle = $request->request->get('libelle');
        $description = $request->request->get('description');
        $coord = $request->request->get('coords');

        //echo "efa tonga" . $coord ;

        // Appeller l API ici avec comme zone $coords de format LatLng(-18.924224, 47.465464),LatLng(-18.921119, 47.465464),LatLng(-18.921119, 47.468769)

       /* $ctrl_message = null;
        if ($coords) {
            $zoneVente = new Zone($libelle, $apropos, $coords);
            echo($zoneVente->getCoordToWKT());

            $intersect = $em->getRepository(Zone::class)->doesPolygonIntersect($zoneVente);

             if($intersect) {
                 $ctrl_message = "Zone intersectee";
             } else {
                 $em->getRepository(Zone::class)->insert($zoneVente);
            }
        }*/
        $zone = new Zone($libelle, $description,$coord);
        //dump($zone->getCoordStrWKT());die();

        $em->getRepository(Zone::class)->insert($zone);

        // return $this->redirectToRoute('nouveau_zv');
        return $this->render('site/new-zone.html.twig', [
            'controller_name' => 'LocationController',
            'message' => "enregistrer",
        ]);
    }

    #[Route('/site/zone', name: 'All_zone')]
    public function listeZone(EntityManagerInterface $entityManager): Response
    {
        $rzone =  $entityManager->getRepository(Zone::class);
        $rsite = $entityManager->getRepository(Site::class);
        $site = $rsite->findAll();

        $zones = $rzone->findAll();
        $zone=(Util::toJson($zones));
        //dump($zone,$site);die();
        return $this->render('site/all-zone.html.twig', [
            'zone' => $zone,
            'zoneName' => $zones,
            'sites' => $site,
        ]);
    }

    
}