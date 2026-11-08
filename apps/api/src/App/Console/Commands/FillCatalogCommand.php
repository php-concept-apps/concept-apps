<?php declare(strict_types=1);

namespace Concept\App\Console\Commands;

use Concept\App\Enums\ProductStatus;
use Concept\Extensions\DatabaseEloquent\Contracts\DatabaseInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Bulk-fill catalog tables for load testing (categories, tags, products, pivots).
 */
final class FillCatalogCommand extends Command
{
    private const string COMMAND_NAME = 'db:fill-catalog';
    private const string COMMAND_DESCRIPTION = 'Fill catalog with N fake products (plus categories/tags)';
    private const string ARG_COUNT = 'count';
    private const string OPT_CHUNK = 'chunk';
    private const string OPT_CATEGORIES = 'categories';
    private const string OPT_TAGS = 'tags';

    private const int DEFAULT_COUNT = 1000;
    private const int DEFAULT_CHUNK = 500;
    private const int DEFAULT_CATEGORIES = 40;
    private const int DEFAULT_TAGS = 80;

    /** @var list<string> */
    private const array CURRENCIES = ['USD', 'EUR', 'UAH', 'GBP'];

    public function __construct(
        private readonly DatabaseInterface $database,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->addArgument(
                self::ARG_COUNT,
                InputArgument::OPTIONAL,
                'Number of products to insert',
                (string) self::DEFAULT_COUNT,
            )
            ->addOption(
                self::OPT_CHUNK,
                null,
                InputOption::VALUE_REQUIRED,
                'Insert chunk size',
                (string) self::DEFAULT_CHUNK,
            )
            ->addOption(
                self::OPT_CATEGORIES,
                null,
                InputOption::VALUE_REQUIRED,
                'How many categories to ensure before products',
                (string) self::DEFAULT_CATEGORIES,
            )
            ->addOption(
                self::OPT_TAGS,
                null,
                InputOption::VALUE_REQUIRED,
                'How many tags to ensure before products',
                (string) self::DEFAULT_TAGS,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = max(1, $this->intOption($input->getArgument(self::ARG_COUNT), self::DEFAULT_COUNT));
        $chunk = max(50, $this->intOption($input->getOption(self::OPT_CHUNK), self::DEFAULT_CHUNK));
        $categoryTarget = max(1, $this->intOption($input->getOption(self::OPT_CATEGORIES), self::DEFAULT_CATEGORIES));
        $tagTarget = max(1, $this->intOption($input->getOption(self::OPT_TAGS), self::DEFAULT_TAGS));

        $connection = $this->database->capsule()->getConnection();
        $faker = FakerFactory::create();

        $io->title(sprintf('Filling catalog with %d products (chunk=%d)', $count, $chunk));

        $categoryIds = $this->ensureCategories($connection, $faker, $categoryTarget, $io);
        $tagIds = $this->ensureTags($connection, $faker, $tagTarget, $io);

        $inserted = 0;
        $pivotRows = 0;
        $started = microtime(true);
        $skuPrefix = 'P' . substr(bin2hex(random_bytes(3)), 0, 6);

        while ($inserted < $count) {
            $batchSize = min($chunk, $count - $inserted);
            $products = [];
            $now = date('Y-m-d H:i:s');

            for ($i = 0; $i < $batchSize; $i++) {
                $n = $inserted + $i + 1;
                $name = $this->words($faker, 3);
                $statuses = ProductStatus::cases();
                $status = $statuses[array_rand($statuses)];
                $products[] = [
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'sku' => sprintf('%s-%07d', $skuPrefix, $n),
                    'name' => ucfirst($name),
                    'slug' => sprintf('%s-%07d', $faker->slug(3), $n),
                    'description' => $this->optionalParagraphs($faker, 2),
                    'price' => $faker->randomFloat(2, 1, 5000),
                    'currency' => self::CURRENCIES[array_rand(self::CURRENCIES)],
                    'stock' => $faker->numberBetween(0, 5000),
                    'status' => $status->value,
                    'weight_grams' => $faker->optional(0.7)->numberBetween(50, 25000),
                    'attributes' => json_encode([
                        'brand' => $faker->company(),
                        'color' => $faker->safeColorName(),
                        'warranty_months' => $faker->randomElement([0, 6, 12, 24, 36]),
                        'origin' => $faker->countryCode(),
                    ], JSON_THROW_ON_ERROR),
                    'published_at' => $status === ProductStatus::Active
                        ? $faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')
                        : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $connection->table('products')->insert($products);
            $firstId = (int) $connection->getPdo()->lastInsertId();
            $productIds = range($firstId, $firstId + $batchSize - 1);

            $pivots = [];
            foreach ($productIds as $productId) {
                $pick = $faker->randomElements($tagIds, $faker->numberBetween(1, min(5, count($tagIds))));
                foreach ($pick as $tagId) {
                    $pivots[] = [
                        'product_id' => $productId,
                        'tag_id' => $tagId,
                    ];
                }
            }

            foreach (array_chunk($pivots, $chunk) as $pivotChunk) {
                $connection->table('product_tag')->insert($pivotChunk);
            }
            $pivotRows += count($pivots);
            $inserted += $batchSize;

            $io->writeln(sprintf('  … %d / %d products', $inserted, $count));
        }

        $seconds = microtime(true) - $started;
        $io->success(sprintf(
            'Done: %d products, %d tag links in %.2fs (%.0f rows/s)',
            $inserted,
            $pivotRows,
            $seconds,
            $seconds > 0 ? $inserted / $seconds : 0,
        ));

        return Command::SUCCESS;
    }

    /**
     * @return list<int>
     */
    private function ensureCategories(Connection $connection, Generator $faker, int $target, SymfonyStyle $io): array
    {
        $existing = $connection->table('categories')->count();
        if ($existing >= $target) {
            /** @var list<int> $ids */
            $ids = $connection->table('categories')->orderBy('id')->limit($target)->pluck('id')->all();
            $io->writeln(sprintf('Categories: reusing %d existing', count($ids)));

            return array_map(intval(...), $ids);
        }

        $need = $target - $existing;
        $io->writeln(sprintf('Categories: creating %d (have %d)', $need, $existing));
        $now = date('Y-m-d H:i:s');
        $roots = [];

        for ($i = 0; $i < $need; $i++) {
            $name = ucfirst($this->words($faker, 2));
            $roots[] = [
                'parent_id' => null,
                'name' => $name,
                'slug' => $faker->unique()->slug(2) . '-' . ($existing + $i + 1),
                'sort_order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($roots, 100) as $chunk) {
            $connection->table('categories')->insert($chunk);
        }

        // Attach ~30% as children of earlier roots for a shallow tree.
        /** @var list<int> $allIds */
        $allIds = $connection->table('categories')->orderBy('id')->pluck('id')->all();
        $allIds = array_map(intval(...), $allIds);
        $updates = 0;
        foreach ($allIds as $index => $id) {
            if ($index === 0 || $faker->boolean(70)) {
                continue;
            }
            $parentId = $allIds[array_rand(array_slice($allIds, 0, $index))];
            $connection->table('categories')->where('id', $id)->update(['parent_id' => $parentId]);
            $updates++;
        }
        $io->writeln(sprintf('Categories: linked %d as children', $updates));

        return $allIds;
    }

    /**
     * @return list<int>
     */
    private function ensureTags(Connection $connection, Generator $faker, int $target, SymfonyStyle $io): array
    {
        $existing = $connection->table('tags')->count();
        if ($existing >= $target) {
            /** @var list<int> $ids */
            $ids = $connection->table('tags')->orderBy('id')->limit($target)->pluck('id')->all();
            $io->writeln(sprintf('Tags: reusing %d existing', count($ids)));

            return array_map(intval(...), $ids);
        }

        $need = $target - $existing;
        $io->writeln(sprintf('Tags: creating %d (have %d)', $need, $existing));
        $now = date('Y-m-d H:i:s');
        $rows = [];
        for ($i = 0; $i < $need; $i++) {
            $name = $faker->unique()->word() . '-' . ($existing + $i + 1);
            $rows[] = [
                'name' => $name,
                'slug' => strtolower($name),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            $connection->table('tags')->insert($chunk);
        }

        /** @var list<int> $ids */
        $ids = $connection->table('tags')->orderBy('id')->pluck('id')->all();

        return array_map(intval(...), $ids);
    }

    private function intOption(mixed $value, int $default): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    private function words(Generator $faker, int $nb): string
    {
        $parts = $faker->words($nb);
        /** @var list<string> $parts */
        $parts = is_array($parts) ? $parts : [$parts];

        return implode(' ', $parts);
    }

    private function optionalParagraphs(Generator $faker, int $nb): ?string
    {
        if (!$faker->boolean(85)) {
            return null;
        }

        $parts = $faker->paragraphs($nb);
        /** @var list<string> $parts */
        $parts = is_array($parts) ? $parts : [$parts];

        return implode("\n\n", $parts);
    }
}
