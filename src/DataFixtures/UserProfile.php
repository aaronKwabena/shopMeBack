<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\UserProfile;
use Faker\Factory;

class UserProfileFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Regular users
        for ($i = 0; $i < 10; $i++) {
            $userProfile = new UserProfile();
            $userProfile->setPhone($faker->phoneNumber)
                ->setAddress($faker->streetAddress)
                ->setCodePostal($faker->postcode)
                ->setCity($faker->city)
                ->setCountry($faker->country);

            $manager->persist($userProfile);  // Fixed the error here
        }

        $manager->flush();
    }
}
