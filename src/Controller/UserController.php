<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Repository\UserRepository;
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

class UserController extends AbstractController
{
    private const USER_ROUTE_PREFIX = '/api/user/{id}';

    #[Route('/api/users', name: 'user', methods: 'GET')]
    public function index(UserRepository $userRepository,SerializerInterface $serializer,): JsonResponse

    {
        $usersList=$userRepository->findAll();
        $jsonUserList = $serializer->serialize($usersList, 'json');

        return new JsonResponse($jsonUserList, Response::HTTP_OK,[], true);
    }

    #[Route(self::USER_ROUTE_PREFIX, name: 'detail_user', methods: ['GET'])]
    public function getDetailUser(SerializerInterface $serializer,User $user,): JsonResponse
    {
        if (!$user) {
            throw new NotFoundHttpException('Cet Utilisateur n\'existe pas.');
        }

        $jsonUser = $serializer->serialize($user, 'json');

        return new JsonResponse($jsonUser, Response::HTTP_OK);
    }

    #[Route(self::USER_ROUTE_PREFIX, name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em, UserRepository $userRepository,
    SerializerInterface $serializer): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'Cet utilisateuur n\existe pas'], Response::HTTP_NOT_FOUND);
        }
       
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/user', name:"create_user", methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer,
    EntityManagerInterface $em,
    UrlGeneratorInterface $urlGenerator,
    ValidatorInterface $validator): JsonResponse
    {

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json');
        
        $location = $urlGenerator->generate('detail_user', ['id' => $user->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route(self::USER_ROUTE_PREFIX, name:"update_user", methods:['PUT'])]

    public function updateUser(Request $request, SerializerInterface $serializer, User $currentUser,
    EntityManagerInterface $em): JsonResponse
   {
       $updatedUser = $serializer->deserialize($request->getContent(),
               User::class,
               'json',
               [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);
       
       $em->persist($updatedUser);
       $em->flush();
       
       return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
   
}

