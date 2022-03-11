<?php

namespace Guave\AssetLoadBundle\Twig;

use Guave\AssetLoadBundle\Helper\AssetHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetLoadExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('assets', [AssetHelper::class, 'assets']),
            new TwigFunction('css', [AssetHelper::class, 'loadCssViaEntrypoints']),
            new TwigFunction('js', [AssetHelper::class, 'loadJsViaEntrypoints']),
            new TwigFunction('loadEntrypoint', [AssetHelper::class, 'loadEntrypoint']),
        ];
    }
}
