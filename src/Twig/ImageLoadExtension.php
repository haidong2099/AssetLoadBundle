<?php

namespace Guave\AssetLoadBundle\Twig;

use Guave\DefaultSiteBundle\Helpers\ImageHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageLoadExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('svg', [ImageHelper::class, 'loadSvg']),
            new TwigFunction('imagePath', [ImageHelper::class, 'getPath']),
            new TwigFunction('resizeImage', [ImageHelper::class, 'resizeImage']),
            new TwigFunction('srcset', [ImageHelper::class, 'generateSrcset']),
            new TwigFunction('srcsetAttr', [ImageHelper::class, 'generateSrcsetAttribute']),
            new TwigFunction('picture', [ImageHelper::class, 'generatePictureElement']),
        ];
    }
}
