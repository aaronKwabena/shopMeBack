<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Repository\UserProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/api", name="api_")
 */
class UserProfileController extends AbstractController
{
    #[Route('/api/user/{id}/profile', name: 'user_profile_get', methods: ['GET'])]
    public function getUserProfile(SerializerInterface $serializer, User $user): JsonResponse
    {
        if (!$user) {
            throw new NotFoundHttpException('Cet utilisateur n\'existe pas.');
        }

        $userProfile = $user->getUserProfile();
        $jsonUserProfile = $serializer->serialize($userProfile, 'json');

        return new JsonResponse($jsonUserProfile, Response::HTTP_OK);
    }

    #[Route('/api/user/{id}/profile', name: 'user_profile_update', methods: ['PUT'])]
    public function updateUserProfile(
        Request $request,
        SerializerInterface $serializer,
        User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$user) {
            throw new NotFoundHttpException('Cet utilisateur n\'existe pas.');
        }

        // Deserialize the user profile data from the request
        $updatedUserProfile = $serializer->deserialize(
            $request->getContent(),
            UserProfile::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user->getUserProfile()]
        );

        // Update the user profile
        $user->setUserProfile($updatedUserProfile);

        $em->persist($updatedUserProfile);
        $em->persist($user);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
