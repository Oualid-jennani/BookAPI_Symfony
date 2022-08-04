<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
		$faker = Faker\Factory::create('fr_FR');
		for ($i = 0; $i < 20; $i++) {
			$book = new Book();
			$book->setCode($faker->uuid);
			$book->setName($faker->name);
			$book->setAuthor($faker->firstName.' '.$faker->lastName);
			$book->setStatus('disponible');
			$book->setCreatedAt(new \DateTime());
			$manager->persist($book);
		}

        $manager->flush();
    }
}
