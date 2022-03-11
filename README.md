# Asset Load Bundle

This contao module allow you to load assets

## Requirements

- Contao 4.9+ (tested up to 4.13)
- PHP 7.4 or 8.0+

## Install

```BASH
$ composer require guave/assetload-bundle
```

## Usage

### Load CSS and JS Files in your templates

Requires a `entrypoints.json` file in your `files/project-name/dist` directory as follows:

```JSON
{
    "entrypoints": {
        "project-name": {
            "css": [
                "/files/project-name/dist/project-name.css"
            ],
            "js": [
                "/files/project-name/dist/project-name.js"
            ]
        }
    }
}
```

and expects a `$GLOBALS['TL_CONFIG']['assetPath']` that contains your `files/project-name` directory

Load the assets into your templates with Twig:

```PHP
{{ css('file-name')|raw }}
{{ js('file-name')|raw }}
```

or with PHP

```PHP
<?php use Guave\AssetLoadBundle\Helper\AssetHelper; ?>
<?= AssetHelper::loadCssViaEntrypoints('file-name') ?>
<?= AssetHelper::loadJsViaEntrypoints('file-name') ?>
```

### Load a Contao image

Get the path from an uploaded image's DB binary with Twig:

```PHP
{{ imagePath('db-binary-string') }}
```

and with PHP:

```PHP
<?php use Guave\AssetLoadBundle\Helper\ImageHelper; ?>
<?= ImageHelper::imagePath('db-binary-string');
```

### Load an SVG image

Use the following in your templates with Twig:

```PHP
{{ svg(TL_ROOT.'files/project-name/images/file-name.svg')|raw }}
```

or with PHP:

```PHP
<?php use Guave\AssetLoadBundle\Helper\ImageHelper; ?>
<?= ImageHelper::loadSvg(TL_ROOT.'files/project-name/images/file-name.svg');
```
