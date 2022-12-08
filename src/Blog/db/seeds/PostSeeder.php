<?php

use Phinx\Seed\AbstractSeed;

class PostSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Seeding des catégories
        $categories = [];
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 5; $i++) {
            $categories[] = [
                'name' => $faker->sentence(1),
                'slug' => $faker->slug(1),
            ];
        }

        $this->table('categories')->insert($categories)->save();

        // Seeding des articles
        $articles = [];
        for ($i = 0; $i < 100; $i++) {
            $date = date('Y-m-d H:i:s', $faker->unixTime('now'));
            $articles[] = [
                'name' => $faker->sentence(4),
                'slug' => $faker->slug(4),
                'content' => $faker->text(3000),
                'created_at' => $date,
                'updated_at' => $date,
                'category_id' => rand(1, 5)
            ];
        }

        $this->table('posts')->insert($articles)->save();
    }
}
