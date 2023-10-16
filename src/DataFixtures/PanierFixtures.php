<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Panier;
use Faker\Factory;
use App\Repository\ProductRepository;

class PanierFixtures extends Fixture
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function load(ObjectManager $manager)
    {
        
        
        // Load Panier fixtures
        $products = $this->productRepository->findAll();

            foreach ($products as $product) {
                $panier = new Panier();
                $panier->setProduct($product);
                $panier->setNameProduct($product->getName());
                $panier->setRefProduct('REF_' . strtoupper(str_replace(' ', '_', $product->getName())));
                $panier->setQuantity(random_int(1, 5));

                $manager->persist($panier);
            }

        $manager->flush();
    }
}

