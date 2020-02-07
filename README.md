# Asset Load Bundle
This contao module adds a Content Element that allows you to use a specific layout.

### Requirements
Contao >4 (tested with 4.8)

### Install
`composer require guave/assetload-bundle`

### Usage
##### Load CSS and JS Files in your templates
Requires a `entrypoints.json` file in your `files/project-name/dist` directory as follows:

```json
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

Load the assets into your templates:
```php
<?php use Guave\AssetLoadBundle\Helper\AssetHelper; ?>
<?= AssetHelper::loadCssViaEntrypoints('file-name') ?>
<?= AssetHelper::loadJsViaEntrypoints('file-name') ?>
```

##### Load an SVG image
Use the following in your templates:
```php
<?php use Guave\AssetLoadBundle\Helper\AssetHelper; ?>
<?= AssetHelper::loadSvg(TL_ROOT.'files/project-name/images/file-name.svg');
```