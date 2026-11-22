<?php declare(strict_types=1);

namespace Concept\App\Database\Seeders;

use Concept\App\Enums\PostStatus;
use Concept\App\Exceptions\RuntimeException;
use Concept\App\Services\SlugService;
use Concept\Extensions\DatabaseEloquent\Contracts\SeederInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection;
use Illuminate\Database\Seeder;

final class BlogContentSeeder extends Seeder implements SeederInterface
{
    private const string ERR_FAKER_MISSING = 'Faker is required for BlogContentSeeder (install fakerphp/faker in require-dev).';
    private const string ERR_NO_USERS = 'BlogContentSeeder requires at least one user (run UserSeeder first).';

    private const int CATEGORY_COUNT = 36;
    private const int TAG_COUNT = 48;
    private const int POST_COUNT = 1024;

    public function __construct(
        private readonly SlugService $slugs,
    ) {}

    public function run(): void
    {
        if (!class_exists(FakerFactory::class)) {
            throw new RuntimeException(self::ERR_FAKER_MISSING);
        }

        $connection = CapsuleManager::schema()->getConnection();
        $faker = FakerFactory::create();

        $authorId = $connection->table('users')->orderBy('id')->value('id');
        if (!is_int($authorId) && !is_string($authorId)) {
            throw new RuntimeException(self::ERR_NO_USERS);
        }
        $authorId = (int) $authorId;

        $this->resetBlogTables($connection);

        $categoryIds = $this->seedCategories($connection, $faker);
        $tagIds = $this->seedTags($connection, $faker);
        $this->seedPosts($connection, $faker, $authorId, $categoryIds, $tagIds);
    }

    private function resetBlogTables(Connection $connection): void
    {
        $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        $connection->table('post_tag')->truncate();
        $connection->table('posts')->truncate();
        $connection->table('tags')->truncate();
        $connection->table('categories')->truncate();
        $connection->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @return list<int>
     */
    private function seedCategories(Connection $connection, Generator $faker): array
    {
        $now = date('Y-m-d H:i:s');
        $ids = [];
        $taken = [];

        for ($i = 1; $i <= self::CATEGORY_COUNT; $i++) {
            $name = ucwords(implode(' ', $this->words($faker, 2)));
            $slug = $this->slugs->unique($name, fn(string $candidate): bool => isset($taken[$candidate]));
            $taken[$slug] = true;
            $ids[] = $connection->table('categories')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'description' => $faker->boolean(70) ? $faker->sentence(12) : null,
                'sort_order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $ids;
    }

    /**
     * @return list<int>
     */
    private function seedTags(Connection $connection, Generator $faker): array
    {
        $now = date('Y-m-d H:i:s');
        $ids = [];
        $taken = [];

        for ($i = 1; $i <= self::TAG_COUNT; $i++) {
            $name = ucfirst($faker->unique()->word());
            $slug = $this->slugs->unique($name, fn(string $candidate): bool => isset($taken[$candidate]));
            $taken[$slug] = true;
            $ids[] = $connection->table('tags')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $ids;
    }

    /**
     * @param list<int> $categoryIds
     * @param list<int> $tagIds
     */
    private function seedPosts(
        Connection $connection,
        Generator $faker,
        int $authorId,
        array $categoryIds,
        array $tagIds,
    ): void {
        $now = date('Y-m-d H:i:s');
        $taken = [];
        $statuses = [
            PostStatus::Published,
            PostStatus::Published,
            PostStatus::Published,
            PostStatus::Draft,
            PostStatus::Archived,
        ];

        for ($i = 1; $i <= self::POST_COUNT; $i++) {
            $title = rtrim($faker->unique()->sentence(), '.');
            $status = $statuses[array_rand($statuses)];
            $publishedAt = $status === PostStatus::Published
                ? $faker->dateTimeBetween('-1 year')->format('Y-m-d H:i:s')
                : null;

            $categoryId = $categoryIds[array_rand($categoryIds)];
            $body = implode("\n\n", $this->paragraphs($faker, $faker->numberBetween(3, 8)));
            $slug = $this->slugs->unique($title, fn(string $candidate): bool => isset($taken[$candidate]));
            $taken[$slug] = true;

            $postId = $connection->table('posts')->insertGetId([
                'user_id' => $authorId,
                'category_id' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $faker->boolean(85) ? $faker->sentence(20) : null,
                'body' => $body,
                'cover_image' => null,
                'status' => $status->value,
                'published_at' => $publishedAt,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $tagCount = $faker->numberBetween(1, min(4, count($tagIds)));
            $pickKeys = (array) array_rand($tagIds, $tagCount);
            foreach ($pickKeys as $key) {
                $connection->table('post_tag')->insert([
                    'post_id' => $postId,
                    'tag_id' => $tagIds[$key],
                ]);
            }
        }
    }

    /** @return list<string> */
    private function words(Generator $faker, int $nb): array
    {
        $words = $faker->unique()->words($nb);
        if (!is_array($words)) {
            return [$faker->word()];
        }

        $result = [];
        foreach ($words as $word) {
            if (is_string($word)) {
                $result[] = $word;
            }
        }

        return $result !== [] ? $result : [$faker->word()];
    }

    /** @return list<string> */
    private function paragraphs(Generator $faker, int $nb): array
    {
        $paragraphs = $faker->paragraphs($nb);
        if (!is_array($paragraphs)) {
            return [$faker->paragraph()];
        }

        $result = [];
        foreach ($paragraphs as $paragraph) {
            if (is_string($paragraph)) {
                $result[] = $paragraph;
            }
        }

        return $result !== [] ? $result : [$faker->paragraph()];
    }
}
