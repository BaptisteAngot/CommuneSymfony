<?php


namespace App\DataFixtures;

use App\Entity\Commune;
use App\Entity\Media;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;


class CommuneFixtures extends Fixture
{

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        //Creation de 20 communes
        for ($i = 0; $i < 20 ; $i++) {
            $faker = Faker\Factory::create('FR-fr');
            $commune = new Commune();
            $commune->setPopulation($faker->numberBetween(100,20000))
                ->setNom($faker->city)
                ->setCode($faker->postcode)
                ->setCodesPostaux([$faker->postcode])
                ->setCodeRegion($faker->postcode)
                ->setCodeDepartement($faker->postcode);
            $manager->persist($commune);

            $media = new Media();
            $media->setUrl($faker->imageUrl($width = 640, $height = 480,"city"))
                ->setCommune($commune);
            $manager->persist($media);
        }
        $manager->flush();
    }
}