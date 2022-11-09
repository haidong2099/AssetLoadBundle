<?php

namespace Guave\AssetLoadBundle\Helper;

use Contao\FilesModel;
use Contao\Image;
use Contao\Image\ImageInterface;
use Contao\Image\PictureConfiguration;
use Contao\Image\PictureConfigurationItem;
use Contao\Image\ResizeConfiguration;
use Contao\Image\ResizeOptions;
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
        $srcset = [];
        $src = '';
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

        if ($ext === 'gif') {
            $src = $image;
        } else {
            if ($image && count($sizes)) {
                try {
                    foreach ($sizes as $i => $size) {
                        $picture = self::getPicture($image, $size, $mode);

                        if ($i == 0) {
                            $src = $picture['img']['src'];
                        }
                        $srcset[] = $picture['img']['src'] . " {$size['width']}w";
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return (object) [
            'srcset' => implode(', ', $srcset),
            'src' => $src,
        ];
    }

    public static function generateSrcsetAttribute($image, $sizes = [], $mode = 'proportional'): string
    {
        $strings = self::generateSrcset($image, $sizes, $mode);

        return 'srcset="' . $strings->srcset . '" src="' . $strings->src . '"';
    }

    public static function generatePictureElement(
        string $path,
        $sizes = [],
        $mode = 'crop',
        $alt = '',
        $objectFit = false,
        $imageClass = ''
    ): string {
        $return = '<picture>';
        $src = '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext == 'gif') {
            $src = $path;
            $path = '';
        }

        if ($path) {
            try {
                foreach ($sizes as $i => $size) {
                    $picture = self::getPicture($path, $size, $mode);
                    $media = $size['breakpoint'] ? "media=\"(min-width: {$size['breakpoint']})\" " : "";

                    if ($i === 0) {
                        $src = $picture['img']['src'];
                    }

                    if ($picture['sources']) {
                        foreach ($picture['sources'] as $source) {
                            $return .= '<source ' . $media . 'srcset="' . $source['src'] . '" type="' . $source['type'] . '">';
                        }
                    } else {
                        $return .= '<source ' . $media . 'srcset="' . $picture['img']['src'] . '">';
                    }
                }
            } catch (\Exception $e) {
            }
        }

        $return .= '<img class="' . $imageClass . '" alt="' . $alt . '" src="' . $src . '"'.$objectFit ? ' data-object-fit' : ''.' loading="lazy"/>';
        $return .= '</picture>';

        return $return;
    }

    private static function getPicture(string $path, array $size, $mode = 'proportional'): array
    {
        $container = System::getContainer();
        $imageFactory = $container->get('contao.image.image_factory');

        if ($path instanceof ImageInterface) {
            $image = $path;
        } else {
            $image = $imageFactory->create(TL_ROOT . '/' . $path);
        }

        $options = new ResizeOptions();
        $config = new PictureConfiguration();

        $resizeConfig = new ResizeConfiguration();
        $resizeConfig->setWidth((int) $size['width']);
        $resizeConfig->setHeight((int) $size['height']);
        $resizeConfig->setMode($mode);
        $configItem = new PictureConfigurationItem();
        $configItem->setResizeConfig($resizeConfig);
        $config->setSize($configItem);

        $formats = [
            'png' => [],
            'jpg' => [],
            'jpeg' => [],
        ];

        if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) {
            foreach ($formats as $k => $format) {
                $formats[$k] = ['webp'];
            }
        } else {
            foreach ($formats as $k => $format) {
                $formats[$k] = [$k];
            }
        }

        $formats['.default'] = ['.default'];

        $config->setFormats($formats);

        $pictureGenerator = $container->get('contao.image.picture_generator');
        $picture = $pictureGenerator->generate($image, $config, $options);

        $rootDir = $container->getParameter('kernel.project_dir');
        $staticUrl = $container->get('contao.assets.files_context')->getStaticUrl();

        return
            [
                'img' => $picture->getImg($rootDir, $staticUrl),
                'sources' => $picture->getSources($rootDir, $staticUrl),
            ];
    }
}
