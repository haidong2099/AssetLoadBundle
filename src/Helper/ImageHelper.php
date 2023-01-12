<?php

namespace Guave\AssetLoadBundle\Helper;

use Contao\CoreBundle\Image\Studio\Studio;
use Contao\FilesModel;
use Contao\Image\PictureConfiguration;
use Contao\Image\PictureConfigurationItem;
use Contao\Image\ResizeConfiguration;
use Contao\System;
use Contao\Validator;
use DOMDocument;

class ImageHelper
{
    public static function resizeImage($image, $width, $height = 0, $mode = 'proportional'): string
    {
        if (empty($image) || '/' === $image) {
            return '';
        }

        $isString = \is_string($image);

        $validatorAdapter = new Validator();
        if (!$isString) {
            return '';
        }

        if ($isString && $validatorAdapter->isUuid($image)) {
            $image = self::getPath($image);
        }

        $image = '/' . ltrim($image, '/');

        $return = self::getPicture($image, ['width' => $width, 'height' => $height], $mode);
        if (isset($return['img'])) {
            return $return['img']['src'];
        }

        return '';
    }

    public static function getPath($image): string
    {
        if (empty($image) || '/' === $image) {
            return '';
        }

        $file = FilesModel::findByUuid($image);

        return $file->path ?? '';
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

    public static function generateSrcsetAttribute($image, $sizes = [], $mode = 'proportional'): string
    {
        $strings = self::generateSrcset($image, $sizes, $mode);

        return 'srcset="' . $strings->srcset . '" src="' . $strings->src . '"';
    }

    public static function generateSrcset($image, $sizes = [], $mode = 'proportional'): object
    {
        $srcset = [];
        $src = '';
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

        if ($ext === 'gif') {
            $src = $image;
        } elseif ($image && count($sizes)) {
            try {
                foreach ($sizes as $i => $size) {
                    $picture = self::getPicture($image, $size, $mode);

                    if ($i === 0) {
                        $src = $picture['img']['src'];
                    }
                    $srcset[] = ($picture['img']['src']) . " {$size['width']}w";
                }
            } catch (\Exception $e) {
            }
        }

        return (object)[
            'srcset' => implode(', ', $srcset),
            'src' => $src,
        ];
    }

    private static function getPicture(string $path, array $size, $mode = 'proportional'): array
    {
        $formats = [
            'jpg' => ['jpg', 'webp'],
            'webp' => ['jpg', 'webp'],
            'png' => ['png', 'webp'],
        ];

        $resizeConfig = (new ResizeConfiguration())
            ->setWidth((int)$size['width'])
            ->setHeight((int)$size['height'])
            ->setMode($mode);
        $configItem = (new PictureConfigurationItem())
            ->setResizeConfig($resizeConfig);
        $picConfig = (new PictureConfiguration())
            ->setSize($configItem)
            ->setFormats($formats);

        $imageFactory = System::getContainer()
            ->get(Studio::class)
            ->createFigureBuilder();

        $figure = $imageFactory
            ->from(TL_ROOT . '/' . $path)
            ->setSize($picConfig)
            ->build();
        $image = $figure->getImage();

        return [
            'img' => $image->getImg(),
            'sources' => $image->getSources(),
        ];
    }

    public static function generatePictureElement(
        string $path,
        $sizes = [],
        $mode = 'crop',
        $imageClass = '',
        $alt = '',
        $objectFit = false
    ): string {
        $return = '<picture>';
        $src = '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'gif') {
            $src = $path;
            $path = '';
        }

        if ($path) {
            try {
                foreach ($sizes as $i => $size) {
                    $picture = self::getPicture($path, $size, $mode);
                    $media = $size['breakpoint'] ? 'media="(min-width: ' . $size['breakpoint'] . ')" ' : '';

                    if ($i === 0) {
                        $src = $picture['img']['src'];
                    }

                    if ($picture['sources']) {
                        foreach ($picture['sources'] as $source) {
                            $return .= '<source ' . $media . 'srcset="/' . $source['src'] . '" type="' . $source['type'] . '">';
                        }
                    } else {
                        $return .= '<source ' . $media . 'srcset="/' . $picture['img']['src'] . '">';
                    }
                }
            } catch (\Exception $e) {
            }
        }

        $objFitAttr = $objectFit ? ' data-object-fit' : '';
        $return .= '<img class="' . $imageClass . '" alt="' . $alt . '" src="/' . $src . '"' . $objFitAttr . ' loading="lazy"/>';
        $return .= '</picture>';

        return $return;
    }
}
