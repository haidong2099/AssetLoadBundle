<?php

namespace Guave\AssetLoadBundle\Helper;

use Contao\System;

class TwigHelper
{
    public static function getAnalyticsId(): ?string
    {
        global $objPage;
        $container = System::getContainer();
        $tagManager = $container->getParameter('googleTagManager');
        if (empty($tagManager)) {
            return null;
        }

        $layout = $objPage->getRelated('layout');
        if (empty($layout)) {
            return null;
        }

        $themeName = $layout->getRelated('pid')->name;
        return $tagManager[$themeName];
    }
}
