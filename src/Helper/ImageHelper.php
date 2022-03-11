<?php

namespace Guave\DefaultSiteBundle\Helpers;

use Contao\FilesModel;
use Contao\Image;
use Contao\Image\ResizeConfiguration;
use Contao\System;
use DOMDocument;

class ImageHelper
{
    public static function getPath($image): string
    {
        if ('' === $image) {
            return '';
        }

        return FilesModel::findByUuid($image)->path;
    }

    public static function resizeImage($image, $width, $height = 0, $mode = 'proportional'): string
    {
        if ('' === $image || '/' === $image) {
            return '';
        }
        $image = '/' . ltrim($image, '/');

        try {
            return Image::getPath(
                System::getContainer()->get('contao.image.image_factory')
                    ->create(
                        TL_ROOT . $image,
                        (new ResizeConfiguration())
                            ->setWidth($width)
                            ->setHeight($height)
                            ->setMode($mode)
                    )->getUrl(TL_ROOT)
            );
        } catch (\InvalidArgumentException $e) {
            return '';
        }
    }

    public static function loadSvg(string $filePath, string $class = '', bool $silent = false)
    {
        $filePath = $filePath[0] === '/' ? $filePath : '/' . $filePath;
        $filePath = TL_ROOT . $filePath;

        if ('' === $filePath || !isset($filePath)) {
            return '';
        }

        if (false === file_exists($filePath)) {
            if ($silent) {
                return '';
            }

            return 'file does not exist: ' . $filePath;
        }

        if ($class) {
            $svg = file_get_contents($filePath);
            $dom = new DOMDocument();
            // this is necessary, because for some reason DOMDocument can't handle the truth!!! I mean SVG ;)
            libxml_use_internal_errors(true);
            $dom->loadHTML($svg);
            foreach ($dom->getElementsByTagName('svg') as $element) {
                $classes = $element->getAttribute('class') ?: '';
                $element->setAttribute('class', "$classes $class");
            }
            return $dom->saveHTML();
        }

        return file_get_contents($filePath);
    }

    public static function generateSrcset($image, $sizes = [], $mode = 'proportional'): object
    {
        $srcset = '';
        $src = '';

        if (count($sizes)) {
            $srcset = array_reduce($sizes, static function ($carry, $item) use ($image, $mode) {
                if ($carry) {
                    $carry .= ', ';
                }
                $carry .= self::resizeImage(
                        $image,
                        $item['width'],
                        $item['height'],
                        $mode
                    ) . ' ' . $item['width'] . 'w';
                return $carry;
            }, '');
            $src = self::resizeImage($image, $sizes[0]['width'], $sizes[0]['height'], $mode);
        }

        return (object)[
            'srcset' => $srcset,
            'src' => $src,
        ];
    }

    public static function generateSrcsetAttribute($image, $sizes = [], $mode = 'proportional'): string
    {
        $strings = self::generateSrcset($image, $sizes, $mode);

        return 'srcset="' . $strings->srcset . '" src="' . $strings->src . '"';
    }

    public static function generatePictureElement(
        $image,
        $sizes = [],
        $mode = 'proportional',
        $alt = '',
        $objectFit = false,
        $imageClass = ''
    ): string {
        $output = '<picture>' . "\n";
        foreach ($sizes as $index => $size) {
            $resizedImage = self::resizeImage($image, $size['width'], $size['height'], 'crop');
            $media = $size['breakpoint'] ? 'media="(min-width: ' . $size['breakpoint'] . ')"' : '';
            $output .= '<source srcset="' . $resizedImage . '" ' . $media . '>' . "\n";
        }

        $resizedImage = self::resizeImage($image, $sizes[0]['width'], $sizes[0]['height'], 'crop');
        $output .= '<img src="' . $resizedImage . '" alt="' . $alt . '" ';
        $output .= $objectFit ? 'data-object-fit' : '';
        $output .= ' class="' . $imageClass . '" loading="lazy">' . "\n";
        $output .= "</picture>";
        return $output;
    }
}
