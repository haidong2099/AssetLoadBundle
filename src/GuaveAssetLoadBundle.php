<?php

namespace Guave\AssetLoadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class GuaveAssetLoadBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
