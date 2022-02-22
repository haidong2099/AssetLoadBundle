<?php

namespace Guave\AssetLoadBundle;

use Guave\AssetLoadBundle\DependencyInjection\GuaveAssetLoadExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GuaveAssetLoadBundle extends Bundle
{
    /**
     * Register extension
     *
     * @return Extension
     */
    public function getContainerExtension(): Extension
    {
        return new GuaveAssetLoadExtension();
    }
}
