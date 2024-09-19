<?php

namespace App\Controller;

use App\Util\Util;
use App\Entity\Site;
use App\Entity\Zone;
use App\Entity\Production;
use App\Form\SiteFormType;
use App\Entity\ProductionMonth;
use App\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;


class SiteController extends AbstractController
{
    #[Route('/site', name: 'app_insert_site')]
    public function index(): Response
    {
        return $this->render('site/index.html.twig', [
        ]);
    }

    #[Route('/site/prediction', name: 'app_prediction_production_site')]
    public function predictionProduction(EntityManagerInterface $entityManager): Response
    {
        /*$data = [
            [
                'station_id' => 43,
                'site_id' => 18,
                'source' => 0,
                'year' => 2024,
                'month' => 7,
                'day' => 9
            ],
            [
                'station_id' => 43,
                'site_id' => 18,
                'source' => 0,
                'year' => 2024,
                'month' => 7,
                'day' => 10
            ]
        ];
       // Appel de l'API Flask
       $client = HttpClient::create();
       $response = $client->request('POST', 'http://127.0.0.1:5000/predict', [
        'json' => $data,

        ]);
       $data = $response->toArray();*/
       $rsite = $entityManager->getRepository(Site::class);
        $site = $rsite->find(2);
        $siteJ = (Util::toJson($site));
        //dump($zoneOfSite,$site);die();
        return $this->render('site/predictionProd.html.twig', [
            'site' => $site,
            'sitej' => $siteJ,
        ]);
    }
    #[Route('site/prediction/production/{id}', name : 'site_production_prediction')]
    public function siteProductionPrediction(Site $site,EntityManagerInterface $entityManager,Request $request): Response
    {   
        $siteName = $site->getLibelle();
        $startDate = $request->getContent(); // Lire les données JSON envoyées
        $data = json_decode($startDate, true);        
        $start = new \DateTime($data['start-date']);
        $end = new \DateTime($data['end-date']);
        $end->modify('+1 day');  // Inclure le dernier jour
    
        $stationId = 43;
        $siteId = 18;
        $source = 0;
    
        $data = [];
        $interval = new \DateInterval('P1D');
        $datePeriod = new \DatePeriod($start, $interval, $end);
    
        foreach ($datePeriod as $date) {
            $data[] = [
                'station_id' => $stationId,
                'site_id' => $siteId,
                'source' => $source,
                'year' => $date->format('Y'),
                'month' => $date->format('m'),
                'day' => $date->format('d'),
            ];
        }
    
        $client = HttpClient::create();
        $response = $client->request('POST', 'http://127.0.0.1:5000/predict', [
            'json' => $data,
        ]);
    
        $rep = $response->toArray();
        $repon = Util::toJson($rep);
        return new JsonResponse($repon);
    }

    #[Route('/site/test', name: 'app_site_test')]
    public function test(EntityManagerInterface $entityManager): Response
    {
        $rproduction = $entityManager->getRepository(ProductionMonth::class);
        $production = $rproduction->findAll();
        $data = [
            [
                'station_id' => 43,
                'site_id' => 18,
                'source' => 0,
                'year' => 2024,
                'month' => 7,
                'day' => 9
            ],
            [
                'station_id' => 43,
                'site_id' => 18,
                'source' => 0,
                'year' => 2024,
                'month' => 7,
                'day' => 10
            ]
        ];
   
       // Appel de l'API Flask
       $client = HttpClient::create();
       $response = $client->request('POST', 'http://127.0.0.1:5000/predict', [
        'json' => $data,

        ]);
       $data = $response->toArray();
       #dump($data);die();
        return $this->render('site/test.html.twig', [
            'productions' => $production,
            'pred' => $data,
        ]);
    }
   
    #[Route('site/production/month/{siteId}', name : 'site_production_month')]
    public function siteProductionMonth($siteId,EntityManagerInterface $entityManager): Response
    {
        $rproduction = $entityManager->getRepository(ProductionMonth::class);
        $production = $rproduction->findBy(['idSite' => $siteId],['mois' => 'ASC']);
        //dump($production);die();
        $rep = Util::toJson($production);
        return new JsonResponse($rep);
    }
    #[Route('/site/production/day/{siteId}', name :'site_production_day')]
    public function getProductionByDay($siteId, EntityManagerInterface $entityManager): Response
    {
        $rsite = $entityManager->getRepository(Production::class);
        $site = $rsite->findBy(
            ['site' => $siteId], // Filtrer par idSite
            ['daty' => 'DESC'],      // Trier par id en ordre décroissant
            10                     // Limiter à 10 résultats
        );
        $taille = count($site);

        //dump($taille); // Voir la taille des résultats
        //die();
        $rep = (Util::toJson($site));
        return new JsonResponse($rep);
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

    #[Route('/updateSite/{id}', name:'app_update_site')]
    public function updateSite(Site $site,Request $req,ManagerRegistry $mr): Response
    {
        $form = $this->createForm(SiteFormType::class,$site);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
            $site = $form->getData();
            //dump($site);die();
            $em = $mr->getManager();

            $em->persist($site);
            $em->flush();

            return $this->redirectToRoute('site_liste');
        }
        return $this->render('site/updateSite.html.twig',[
            'form'=>$form->createView()
        ]);
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
    #[Route('/site/{id}', name: 'detail_site')]
    public function detailSite($id,EntityManagerInterface $entityManager): Response
    {
        $rsite = $entityManager->getRepository(Site::class);
        $site = $rsite->find($id);
        $rzone = $entityManager->getRepository(Zone::class);
        $siteJ = (Util::toJson($site));
        //dump($site->getIncidents()[0]->getLibelle());die();
        //dump($site,$siteJ);die();
        $zoneOfSite = $rzone->getZoneOfSite($id);
        //dump($zoneOfSite);die();
        //$zone = (Util::toJson($zoneOfSite));

        //dump($zoneOfSite,$site);die();
        return $this->render('site/detailSite.html.twig', [
            'site' => $site,
            'sitej' => $siteJ,
            'zonej' => $zoneOfSite,
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
