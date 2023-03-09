<?php

declare(strict_types=1);

namespace Guave\AssetLoadBundle\Twig;

use Contao\CoreBundle\Twig\Inheritance\TemplateHierarchyInterface;
use Guave\AssetLoadBundle\Helper\TwigHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Path;
use Twig\TwigFunction;

class TwigTemplateExtension extends \Twig\Extension\AbstractExtension
{
    private TemplateHierarchyInterface $hierarchy;

    public function __construct(TemplateHierarchyInterface $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('dynamic_template_path', [$this, 'getDynamicThemePath']),
            new TwigFunction('theme_slug', [$this, 'getThemeSlug']),
            new TwigFunction('analytics_id', [TwigHelper::class, 'getAnalyticsId']),
        ];
    }

    public function getDynamicThemePath(string $template, string $theme = null): string
    {
        if (!$theme) {
            $theme = $this->getThemeSlug();
        }
        $chains = $this->hierarchy->getInheritanceChains($theme);

        if (!empty($chains) && key_exists($template, $chains)) {
            return array_shift($chains[$template]);
        }

        return $template;
    }

    public function getThemeSlug(): ?string
    {
        global $objPage;

        if ($objPage && $objPage->templateGroup) {
            return substr($objPage->templateGroup, 10); // remove templates/
        }

        return null;
    }
}
