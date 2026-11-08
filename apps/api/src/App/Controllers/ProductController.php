<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Enums\ProductStatus;
use Concept\App\Foundation\ConfigKey;
use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\App\Http\Requests\StoreProductRequest;
use Concept\App\Http\Requests\UpdateProductRequest;
use Concept\App\Models\Category;
use Concept\App\Models\Product;
use Concept\App\Models\Tag;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\Http\Protocol\HttpStatusCode;
use Illuminate\Database\Eloquent\Builder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProductController
{
    private const string QUERY_PER_PAGE = 'per_page';
    private const string QUERY_STATUS = 'status';
    private const string QUERY_CATEGORY_ID = 'category_id';
    private const string QUERY_Q = 'q';

    private const int DEFAULT_PER_PAGE = 20;
    private const int DEFAULT_MAX_PER_PAGE = 100;

    private const string ERR_NOT_FOUND = 'Product not found';
    private const string ERR_CATEGORY_NOT_FOUND = 'Category not found';
    private const string ERR_SKU_TAKEN = 'SKU already exists';
    private const string ERR_SLUG_TAKEN = 'Slug already exists';
    private const string ERR_TAGS_INVALID = 'One or more tag_ids are invalid';

    /** @var list<string> */
    private const array PRODUCT_RELATIONS = [
        'category:id,name,slug,parent_id',
        'tags:id,name,slug',
    ];

    public function __construct(
        private readonly ApiResponseFactoryInterface $apiResponseFactory,
        private readonly ConfigInterface $config,
    ) {}

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array<string, mixed> $params */
        $params = $request->getQueryParams();
        $perPage = $this->resolvePerPage($params);

        /** @var Builder<Product> $query */
        $query = Product::query()
            ->with(self::PRODUCT_RELATIONS)
            ->orderByDesc('id');

        $status = $params[self::QUERY_STATUS] ?? null;
        if (is_string($status)) {
            $productStatus = ProductStatus::tryFrom($status);
            if ($productStatus !== null) {
                $query->where('status', $productStatus);
            }
        }

        $categoryId = $params[self::QUERY_CATEGORY_ID] ?? null;
        if (is_numeric($categoryId)) {
            $query->where('category_id', (int) $categoryId);
        }

        $search = $params[self::QUERY_Q] ?? null;
        if (is_string($search) && $search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(static function(Builder $builder) use ($like): void {
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            });
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        return $this->apiResponseFactory->paginated($paginator);
    }

    public function show(int $id): ResponseInterface
    {
        $product = $this->findProduct($id);
        if ($product === null) {
            return $this->apiResponseFactory->error(self::ERR_NOT_FOUND, HttpStatusCode::NOT_FOUND);
        }

        return $this->apiResponseFactory->success($product->toArray());
    }

    public function store(StoreProductRequest $request): ResponseInterface
    {
        $data = $this->validatedInput($request);
        $hasTagIds = array_key_exists('tag_ids', $data);
        $tagIds = $this->extractTagIds($data);
        unset($data['tag_ids']);

        $conflict = $this->assertWritable($data);
        if ($conflict !== null) {
            return $conflict;
        }

        if ($hasTagIds) {
            $tagError = $this->assertTagsExist($tagIds);
            if ($tagError !== null) {
                return $tagError;
            }
        }

        $product = Product::query()->create($data);
        if ($hasTagIds) {
            $product->tags()->sync($tagIds);
        }
        $product->load(self::PRODUCT_RELATIONS);

        return $this->apiResponseFactory->success($product->toArray(), HttpStatusCode::CREATED);
    }

    public function update(int $id, UpdateProductRequest $request): ResponseInterface
    {
        $product = $this->findProduct($id);
        if ($product === null) {
            return $this->apiResponseFactory->error(self::ERR_NOT_FOUND, HttpStatusCode::NOT_FOUND);
        }

        $data = $this->validatedInput($request);
        $hasTagIds = array_key_exists('tag_ids', $data);
        $tagIds = $this->extractTagIds($data);
        unset($data['tag_ids']);

        if ($data === [] && !$hasTagIds) {
            return $this->apiResponseFactory->success($product->toArray());
        }

        $conflict = $this->assertWritable($data, $this->productId($product));
        if ($conflict !== null) {
            return $conflict;
        }

        if ($hasTagIds) {
            $tagError = $this->assertTagsExist($tagIds);
            if ($tagError !== null) {
                return $tagError;
            }
        }

        if ($data !== []) {
            $product->fill($data);
            $product->save();
        }

        if ($hasTagIds) {
            $product->tags()->sync($tagIds);
        }

        $product->load(self::PRODUCT_RELATIONS);

        return $this->apiResponseFactory->success($product->toArray());
    }

    public function destroy(int $id): ResponseInterface
    {
        $product = Product::query()->find($id);
        if (!($product instanceof Product)) {
            return $this->apiResponseFactory->error(self::ERR_NOT_FOUND, HttpStatusCode::NOT_FOUND);
        }

        $product->delete();

        return $this->apiResponseFactory->success([
            'deleted' => true,
            'id' => $id,
        ]);
    }

    private function findProduct(int $id): ?Product
    {
        $product = Product::query()->with(self::PRODUCT_RELATIONS)->find($id);

        return $product instanceof Product ? $product : null;
    }

    private function productId(Product $product): int
    {
        $key = $product->getKey();
        if (!is_int($key) && !is_numeric($key)) {
            return 0;
        }

        return (int) $key;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function assertWritable(array $data, ?int $ignoreProductId = null): ?ResponseInterface
    {
        if (isset($data['category_id']) && is_numeric($data['category_id'])) {
            $categoryId = (int) $data['category_id'];
            if (!Category::query()->whereKey($categoryId)->exists()) {
                return $this->apiResponseFactory->error(self::ERR_CATEGORY_NOT_FOUND, HttpStatusCode::UNPROCESSABLE_ENTITY);
            }
        }

        if (isset($data['sku']) && is_string($data['sku'])) {
            $skuQuery = Product::query()->where('sku', $data['sku']);
            if ($ignoreProductId !== null) {
                $skuQuery->where('id', '!=', $ignoreProductId);
            }
            if ($skuQuery->exists()) {
                return $this->apiResponseFactory->error(self::ERR_SKU_TAKEN, HttpStatusCode::CONFLICT);
            }
        }

        if (isset($data['slug']) && is_string($data['slug'])) {
            $slugQuery = Product::query()->where('slug', $data['slug']);
            if ($ignoreProductId !== null) {
                $slugQuery->where('id', '!=', $ignoreProductId);
            }
            if ($slugQuery->exists()) {
                return $this->apiResponseFactory->error(self::ERR_SLUG_TAKEN, HttpStatusCode::CONFLICT);
            }
        }

        return null;
    }

    /**
     * Keep only keys present in the request body (PATCH-friendly).
     *
     * @return array<string, mixed>
     */
    private function validatedInput(StoreProductRequest|UpdateProductRequest $request): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $body = $request->httpRequest()->getParsedBody();
        if (!is_array($body)) {
            return $validated;
        }

        /** @var array<string, mixed> $filtered */
        $filtered = array_intersect_key($validated, $body);

        return $filtered;
    }

    /**
     * @param array<string, mixed> $data
     * @return list<int>
     */
    private function extractTagIds(array &$data): array
    {
        $raw = $data['tag_ids'] ?? null;
        if (!is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $value) {
            if (is_numeric($value)) {
                $ids[] = (int) $value;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param list<int> $tagIds
     */
    private function assertTagsExist(array $tagIds): ?ResponseInterface
    {
        if ($tagIds === []) {
            return null;
        }

        $validCount = Tag::query()->whereIn('id', $tagIds)->count();
        if ($validCount !== count($tagIds)) {
            return $this->apiResponseFactory->error(self::ERR_TAGS_INVALID, HttpStatusCode::UNPROCESSABLE_ENTITY);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function resolvePerPage(array $params): int
    {
        $default = $this->config->getInt(ConfigKey::PAGINATION_PER_PAGE, self::DEFAULT_PER_PAGE);
        $max = $this->config->getInt(ConfigKey::PAGINATION_MAX_PER_PAGE, self::DEFAULT_MAX_PER_PAGE);

        $requested = $params[self::QUERY_PER_PAGE] ?? $default;
        if (!is_numeric($requested)) {
            return max(1, $default);
        }

        return max(1, min((int) $requested, max(1, $max)));
    }
}
