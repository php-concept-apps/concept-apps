<?php declare(strict_types=1);

namespace Concept\App\Controllers;

use Concept\App\Foundation\ConfigKey;
use Concept\App\Http\Contracts\ApiResponseFactoryInterface;
use Concept\App\Models\Product;
use Concept\Extensions\Config\Contracts\ConfigInterface;
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

    /** @var list<string> */
    private const array ALLOWED_STATUSES = [
        Product::STATUS_DRAFT,
        Product::STATUS_ACTIVE,
        Product::STATUS_ARCHIVED,
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
            ->with([
                'category:id,name,slug,parent_id',
                'tags:id,name,slug',
            ])
            ->orderByDesc('id');

        $status = $params[self::QUERY_STATUS] ?? null;
        if (is_string($status) && in_array($status, self::ALLOWED_STATUSES, true)) {
            $query->where('status', $status);
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
