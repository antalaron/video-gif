<?php

/*
 * This file is part of VideoGif.
 *
 * (c) Antal Áron <antalaron@antalaron.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antalaron\VideoGif;

use Antalaron\VideoGif\Exception\VideoException;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use GifCreator\GifCreator;
use Gregwar\Image\Image;

/**
 * VideoGif.
 *
 * @author Antal Áron <antalaron@antalaron.hu>
 */
class VideoGif
{
    const VERSION = '0.1.0';

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var array
     */
    private $defaults = [
        'width' => 720,
        'height' => 404,
        'count' => 5,
        'interval' => 50,
    ];

    /**
     * Constructor.
     *
     * @param string $tmpDir   Temp direcory path
     * @param array  $defaults The defaults
     */
    public function __construct($tmpDir = '/tmp', $defaults = [])
    {
        $this->tmpDir = ltrim($tmpDir, '/');
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    /**
     * Create.
     *
     * This method creates the thumbnail from the video.
     *
     * @param string $videoFile     Video file to process
     * @param string $thumbnailFile Output gif
     * @param int    $count         Number of frames to use
     * @param int    $interval      The interval beetween the frames of the gif
     * @param int    $width         Width of the gif
     * @param int    $height        Height of the gif
     *
     * @throws VideoException If error during procession or writing.
     */
    public function create($videoFile, $thumbnailFile, $count = null, $interval = null, $width = null, $height = null)
    {
        $count = $count ?: $this->defaults['count'];
        $interval = $interval ?: $this->defaults['interval'];
        $width = $width ?: $this->defaults['width'];
        $height = $height ?: $this->defaults['height'];

        $ffmpeg = FFMpeg::create();
        $ffprobe = FFProbe::create();

        try {
            // Determine the duration of the video
            $duration = $ffprobe
                ->format($videoFile)
                ->get('duration');
        } catch (\Exception $e) {
            throw new VideoException(sprintf('Cannot determine the duration of %s', $videoFile), 0, $e);
        }

        $delay = (float) $duration / ($count + 1);

        $video = $ffmpeg->open($videoFile);
        if (!file_exists($this->tmpDir.'/video-gif')) {
            mkdir($this->tmpDir.'/video-gif', 0777, true);
        }

        $hash = md5($videoFile.time());

        $hashDir = $this->tmpDir.'/video-gif/'.$hash;
        if (!file_exists($hashDir)) {
            mkdir($hashDir, 0777, true);
        }

        $pos = 0;
        $frames = [];
        $durations = [];

        // Grab frames
        for ($i = 0; $i < $count; ++$i) {
            $pos += $delay;
            $video
                ->frame(TimeCode::fromSeconds($pos))
                ->save(sprintf($hashDir.'/tmp-frame%03d.jpg', $i));

            Image::open(sprintf($hashDir.'/tmp-frame%03d.jpg', $i))
                 ->cropResize($width, $height)
                 ->save(sprintf($hashDir.'/frame%03d.jpg', $i));

            $frames[] = sprintf($hashDir.'/frame%03d.jpg', $i);
            $durations[] = $interval;
        }

        $gc = new GifCreator();
        $gc->create($frames, $durations, 0);

        $this->removeDirectory($hashDir);

        if (false === @file_put_contents($thumbnailFile, $gc->getGif())) {
            throw new VideoException(sprintf('Cannot write %s', $thumbnailFile));
        }
    }

    /**
     * Remove directory.
     *
     * @param string $path Path to remove
     */
    private function removeDirectory($path)
    {
        $files = glob($path.'/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->removeDirectory($file) : unlink($file);
        }

        rmdir($path);
    }
}
