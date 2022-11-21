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
        $data = [];
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 100; $i++) {
            $date = date('Y-m-d H:i:s', $faker->unixTime('now'));
            $data[] = [
                'name' => $faker->sentence(4),
                'slug' => $faker->slug(4),
                'content' => $faker->text(3000),
                'created_at' => $date,
                'updated_at' => $date,
            ];
        }

        $this->table('posts')->insert($data)->save();
    }
}
