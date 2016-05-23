# VideoGif

A PHP library to create animated gif thumbnails for videos.

## Good to know

### How this library works

This library requires a working FFMpeg install. You will need both FFMpeg and FFProbe binaries to use it.
Be sure that these binaries can be located with system PATH.

### Known issues :

- _to be written_

## Installation

The recommended way to install VideoGif is through [Composer](https://getcomposer.org).

```json
{
    "require": {
        "antalaron/video-gif": "~0.1"
    }
}
```

## Basic Usage

```php
$videoGif = new Antalaron\VideoGif\VideoGif();

$videoGif->create('path/to/video.mp4', 'path/to/video.gif');
```

## License

This project is licensed under the [MIT license](http://opensource.org/licenses/MIT).
