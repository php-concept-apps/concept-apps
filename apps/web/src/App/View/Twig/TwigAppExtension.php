<?php declare(strict_types=1);

namespace Concept\App\View\Twig;

use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\Http\Contracts\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigAppExtension extends AbstractExtension
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    public function getFilters(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', [$this->config, 'get']),
            new TwigFunction('uri', [$this->urlGenerator, 'uri']),
            new TwigFunction('url', [$this->urlGenerator, 'url']),
            new TwigFunction('base_url', [$this->urlGenerator, 'base']),
        ];
    }
}
