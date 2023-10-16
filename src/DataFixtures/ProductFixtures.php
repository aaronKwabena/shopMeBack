<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Product;
use Faker\Factory;
use App\Repository\ProductRepository;

class ProductFixtures extends Fixture
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        // Predefined real product names for each category
        $productNames = [
            'Fruits' => ['Apples', 'Oranges', 'Bananas', 'Grapes', 'Strawberries'],
            'Légumes' => ['Carrots', 'Broccoli', 'Potatoes', 'Spinach', 'Tomatoes'],
            'Produits laitiers' => ['Milk', 'Cheese', 'Yogurt', 'Butter'],
            'Réfrigérés' => ['Eggs', 'Fresh Juice', 'Prepared Salads'],
            'Petit-déjeuner' => ['Cereals', 'Bread', 'Jam', 'Honey'],
            'Boissons' => ['Water', 'Juice', 'Soda'],
            'Collations' => ['Chips', 'Nuts', 'Granola Bars', 'Chocolate']
        ];

        $productCategories = array_keys($productNames);

        for ($i = 0; $i < 40; $i++) {
            $category = $faker->randomElement($productCategories);
            $productName = $faker->randomElement($productNames[$category]);

            $product = (new Product())
                ->setName($productName)
                ->setPrice($faker->randomFloat(2, 1, 100))
                ->setType($category)
                ->setDescription($faker->sentence)
                ->setCategory($category)
                ->setImage($faker->imageUrl());

            $manager->persist($product);
        }

        $manager->flush();
    }
}
