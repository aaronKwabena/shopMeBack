<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
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

class ProductController extends AbstractController
{
    private const PRODUCT_ROUTE_PREFIX = '/api/product/{id}';

    #[Route('/api/products', name: 'product', methods: 'GET')]
    public function index(ProductRepository $productRepository,SerializerInterface $serializer,): JsonResponse

    {
        $productsList=$productRepository->findAll();
        $jsonProductList = $serializer->serialize($productsList, 'json');

        return new JsonResponse($jsonProductList, Response::HTTP_OK,[], true);
    }

    #[Route(self::PRODUCT_ROUTE_PREFIX, name: 'detail_product', methods: ['GET'])]
    public function getDetailProduct(SerializerInterface $serializer,Product $product,): JsonResponse
    {
        if (!$product) {
            throw new NotFoundHttpException('Ce Produit n\'existe pas.');
        }

        $jsonProduct = $serializer->serialize($product, 'json');

        return new JsonResponse($jsonProduct, Response::HTTP_OK);
    }

    #[Route(self::PRODUCT_ROUTE_PREFIX, name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct(Product $product, EntityManagerInterface $em, ProductRepository $uproductRepository,
    SerializerInterface $serializer): JsonResponse
    {
        if (!$product) {
            return new JsonResponse(['error' => 'Ce prodit n\existe pas'], Response::HTTP_NOT_FOUND);
        }
       
        $em->remove($product);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/product', name:"create_product", methods: ['POST'])]
    public function createProduct(Request $request, SerializerInterface $serializer,
    EntityManagerInterface $em,
    UrlGeneratorInterface $urlGenerator,
    ValidatorInterface $validator): JsonResponse
    {

        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $em->persist($product);
        $em->flush();

        $jsonProduct = $serializer->serialize($product, 'json');
        
        $location = $urlGenerator->generate('detail_product', ['id' => $product->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route(self::PRODUCT_ROUTE_PREFIX, name:"update_product", methods:['PUT'])]

    public function updateProduct(Request $request, SerializerInterface $serializer, Product $currentProduct,
    EntityManagerInterface $em): JsonResponse
   {
       $updatedProduct = $serializer->deserialize($request->getContent(),
               Product::class,
               'json',
               [AbstractNormalizer::OBJECT_TO_POPULATE => $currentProduct]);
       
       $em->persist($updatedProduct);
       $em->flush();
       
       return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

}
