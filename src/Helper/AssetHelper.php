<?php

namespace Guave\AssetLoadBundle\Helper;

use Contao\System;

class AssetHelper
{
    /**
     * @param string $filePath
     * 
     * @return false|string
     */
    public static function loadSvg(string $filePath)
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');
        $filePath = substr($filePath, 0, 1) == '/' ? $filePath : '/'.$filePath;
        $filePath = $rootDir . $filePath;
        
        if ('' == $filePath || !isset($filePath) || !is_string($filePath)) {
            return '';
        }
        
        if (false == file_exists($filePath)) {
            return 'file does not exist: '.$filePath;
        }
        
        return file_get_contents($filePath);
    }

    /**
     * @param $entrypoint
     * @param $ressourceType
     * 
     * @return string
     * 
     * @throws \Exception
     */
    public static function loadEntrypoint($entrypoint, $ressourceType): string
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');
        $path = $rootDir.'/'.$GLOBALS['TL_CONFIG']['assetPath'].'/dist/entrypoints.json';
        
        if (!file_exists($path)) {
            throw new \Exception('entrypoints.json not found. did you run the build?');
        }
        
        $entrypoints = json_decode(file_get_contents($path), true);
        if (!isset($entrypoints['entrypoints'][$entrypoint][$ressourceType])) {
            return "<!-- WARNING: {$entrypoint} not found in entrypoints.json for {$ressourceType} -->";
        }
        
        $resources = [];
        foreach ($entrypoints['entrypoints'][$entrypoint][$ressourceType] as $path) {
            $resources[] = self::renderResource($ressourceType, $path);
        }
        
        return implode("", $resources);
    }

    /**
     * @param $entrypoint
     * 
     * @return string
     * 
     * @throws \Exception
     */
    public static function loadJsViaEntrypoints($entrypoint): string
    {
        return self::loadEntrypoint($entrypoint, 'js');
    }

    /**
     * @param $entrypoint
     * 
     * @return string
     * 
     * @throws \Exception
     */
    public static function loadCssViaEntrypoints($entrypoint): string
    {
        return self::loadEntrypoint($entrypoint, 'css');
    }

    /**
     * @param $type
     * @param $path
     * 
     * @return string|null
     */
    protected static function renderResource($type, $path): ?string
    {
        $hash = \System::getContainer()->getParameter('githash');

        if ($hash) {
            $version = '?version=' . $hash;
        } else {
            $version = '';
        }

        switch($type) {
            case 'css':
                return "<link type=\"text/css\" href=\"{$path}{$version}\" rel=\"stylesheet\">\n";
            case 'js':
                return "<script src=\"{$path}{$version}\"></script>\n";
            default:
                return "<!-- don't know how to render '{$type}' -->\n";
        }
    }
}