# Asset Load Bundle

This contao module allow you to load assets

## Requirements

- Contao 5.2.*
- contao/core-bundle 5.2.10
- PHP 8.2.15

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

### Load an SVG image

Use the following in your templates with Twig:

```PHP
{{ svg(TL_ROOT.'files/project-name/images/file-name.svg')|raw }}
```

or with PHP:

```PHP
<?php use Guave\AssetLoadBundle\Helper\AssetHelper; ?>
<?= AssetHelper::loadSvg(TL_ROOT.'files/project-name/images/file-name.svg') ?>
```

### get dynamic Template Path

dynamic by active theme

```PHP
{% extends dynamic_template_path('base') %}
```

dynamic by theme

```PHP
{% extends dynamic_template_path('base', 'test') %}
```

## Deprecations

The ImageHelper is now deprecated, it's recommended to use the [Contao Image Studio](https://docs.contao.org/dev/framework/image-processing/image-studio/).

Define sizes in config.yml and use in contao_figure with image path or ID:

```YAML
contao:
  image:
    sizes:
      _defaults:
        formats:
          jpg: [ webp, jpg ]
          webp: [ webp, jpg ]
          png: [ webp, png ]
        resize_mode: crop
        densities: 1.5x, 2x
        lazy_loading: true

      large_photo:
        width: 1000
        height: 500

      medium_photo:
        width: 500
        height: 250

      small_box:
        width: 100
        height: 100
        resize_mode: box
        densities: 2x
```

```PHP
{{ contao_figure('path/to/my/image.png', '_medium_photo') }}
```

Define sizes dynamically in contao_figure directly:

```PHP
{{ contao_figure('image_id', [200, 200, 'proportional'], { 
  metadata: { alt: 'Contao Logo', caption: 'Look at this CMS!' },
  enableLightbox: true,
  lightboxGroupIdentifier: 'logos',
  lightboxSize: '_large_photo',
  linkHref: 'https://contao.org',
  options: { attr: { class: 'logo-container' } }
}) }}
```
