<?php

namespace Guave\AssetLoadBundle\Helper;

use Contao\System;
use Exception;
use RuntimeException;

class AssetHelper
{
    /**
     * @param string $fileName
     *
     * @return string
     */
    public static function assets(string $fileName): string
    {
        $filesDir = System::getContainer()->getParameter('contao.localconfig')['assetPath'];
        $assetPath = $filesDir . "/" . $fileName;
        $manifest = json_decode(
            file_get_contents(
                implode("/", [$_SERVER['DOCUMENT_ROOT'], $filesDir, 'dist', 'manifest.json'])
            ),
            true
        );

        return $manifest[$assetPath];
    }

    /**
     * @throws Exception
     */
    public static function loadJsViaEntrypoints($entrypoint, $parameters = []): string
    {
        return self::loadEntrypoint($entrypoint, 'js', $parameters);
    }

    /**
     * @throws Exception
     */
    public static function loadCssViaEntrypoints($entrypoint, $parameters = []): string
    {
        return self::loadEntrypoint($entrypoint, 'css', $parameters);
    }

    public static function loadEntrypoint($entrypoint, $resourceType, $parameters = []): string
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');
        $assetPath = System::getContainer()->getParameter('contao.localconfig')['assetPath'];
        $path = $rootDir . '/' . $assetPath . '/dist/entrypoints.json';

        if (!file_exists($path)) {
            throw new RuntimeException('entrypoints.json not found. did you run the build?');
        }

        $entrypoints = json_decode(file_get_contents($path), true);
        if (!isset($entrypoints['entrypoints'][$entrypoint][$resourceType])) {
            return "<!-- WARNING: {$entrypoint} not found in entrypoints.json for {$resourceType} -->";
        }

        $resources = [];
        foreach ($entrypoints['entrypoints'][$entrypoint][$resourceType] as $path) {
            $resources[] = self::renderResource($resourceType, $path, $parameters);
        }

        return implode("", $resources);
    }

    protected static function renderResource($type, $path): string
    {
        $hash = System::getContainer()->getParameter('contao.localconfig')['gitHash'];
        if ($hash) {
            $version = '?version=' . $hash;
        } else {
            $version = '';
        }

        switch ($type) {
            case 'css':
                return '<link type="text/css" href="' . $path . $version . '" rel="stylesheet">' . "\n";
            case 'js':
                return '<script src="' . $path . $version . '"></script>' . "\n";
            default:
                return "<!-- don't know how to render '{$type}' -->\n";
        }
    }
}
