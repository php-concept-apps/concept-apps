<?php declare(strict_types=1);

namespace Concept\App\Controllers\Admin;

use Concept\Extensions\View\Contracts\ViewResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class DashboardController
{
    public function __construct(
        private readonly ViewResponseFactoryInterface $view,
    ) {}

    public function index(): ResponseInterface
    {
        return $this->view->create('@dashboard/home');
    }
}
