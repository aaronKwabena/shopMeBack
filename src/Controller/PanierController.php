<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * @Route("/api", name:"api_")
 */

class PanierController extends AbstractController
{
    private const PANIER_ROUTE_PREFIX = '/api/panier/{id}';

    #[Route('/api/paniers', name: 'panier', methods: 'GET')]
    public function index(PanierRepository $panierRepository,SerializerInterface $serializer,): JsonResponse

    {
        $paniersList=$panierRepository->findAll();
        $jsonPanierList = $serializer->serialize($paniersList, 'json');

        return new JsonResponse($jsonPanierList, Response::HTTP_OK,[], true);
    }

    #[Route(self::PANIER_ROUTE_PREFIX, name: 'detail_panier', methods: ['GET'])]
    public function getDetailPanier(SerializerInterface $serializer,Panier $panier,): JsonResponse
    {
        if (!$panier) {
            throw new NotFoundHttpException('Ce panier n\'existe pas.');
        }

        $jsonPanier = $serializer->serialize($panier, 'json');

        return new JsonResponse($jsonPanier, Response::HTTP_OK);
    }

    #[Route(self::PANIER_ROUTE_PREFIX, name: 'delete_panier', methods: ['DELETE'])]
    public function deletePanier(Panier $panier, EntityManagerInterface $em, PanierRepository $panierRepository,
    SerializerInterface $serializer): JsonResponse
    {
       
        if (!$panier) {
            return new JsonResponse(['error' => 'Ce panier n\'existe pas.'], Response::HTTP_NOT_FOUND);
        }
       
        $em->remove($panier);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/panier', name:"create_panier", methods: ['POST'])]
    public function createPanier(Request $request, SerializerInterface $serializer,
    EntityManagerInterface $em,
    UrlGeneratorInterface $urlGenerator,
    ValidatorInterface $validator): JsonResponse
    {

        $panier = $serializer->deserialize($request->getContent(), Panier::class, 'json');

        $errors = $validator->validate($panier);
        if (count($errors) > 0) {
            
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $em->persist($panier);
        $em->flush();

        $jsonPanier = $serializer->serialize($panier, 'json');
        
        $location = $urlGenerator->generate('detail_panier', ['id' => $panier->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonPanier, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route(self::PANIER_ROUTE_PREFIX, name:"update_panier", methods:['PUT'])]

    public function updatePanier(Request $request, SerializerInterface $serializer, Panier $currentPanier,
    EntityManagerInterface $em): JsonResponse
   {
       $updatedPanier = $serializer->deserialize($request->getContent(),
               Eleve::class,
               'json',
               [AbstractNormalizer::OBJECT_TO_POPULATE => $currentPanier]);
       
       $em->persist($updatedPanier);
       $em->flush();

       return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
   
}
