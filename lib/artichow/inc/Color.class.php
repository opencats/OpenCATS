<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */

/**
 * Create your colors
 *
 * @package Artichow
 */
class awColor
{
    public $red;

    public $green;

    public $blue;

    public $alpha;

    private $resource;

    private $color;

    /**
     * Build your color
     *
     * @var int Red intensity (from 0 to 255)
     * @var int Green intensity (from 0 to 255)
     * @var int Blue intensity (from 0 to 255)
     * @var int Alpha channel (from 0 to 100)
     */
    public function __construct($red, $green, $blue, $alpha = 0)
    {
        $this->red = (int) $red;
        $this->green = (int) $green;
        $this->blue = (int) $blue;
        $this->alpha = (int) round($alpha * 127 / 100);
    }

    /**
     * Return a GDised color
     *
     * @param resource $resource A GD resource
     * @return int
     */
    public function getColor($resource)
    {
        $this->resource = $resource;

        if ($this->color === null) {
            if ($this->alpha === 0 or function_exists('imagecolorallocatealpha') === false) {
                $this->color = imagecolorallocate($this->resource, $this->red, $this->green, $this->blue);
            } else {
                $this->color = imagecolorallocatealpha($this->resource, $this->red, $this->green, $this->blue, $this->alpha);
            }
        }

        return $this->color;
    }

    /**
     * Change color brightness
     *
     * @param int $brightness Add this intensity to the color (betweeen -255 and +255)
     */
    public function brightness($brightness)
    {
        $brightness = (int) $brightness;

        $this->red = min(255, max(0, $this->red + $brightness));
        $this->green = min(255, max(0, $this->green + $brightness));
        $this->blue = min(255, max(0, $this->blue + $brightness));
    }

    /**
     * Get RGB and alpha values of your color
     *
     * @return array
     */
    public function rgba()
    {
        return [$this->red, $this->green, $this->blue, $this->alpha];
    }

    /**
     * Free resources used for this color
     */
    public function free()
    {
        if ($this->resource !== null) {
            @imagecolordeallocate($this->resource, $this->color);
            $this->resource = null;
        }
    }

    public function __destruct()
    {
        $this->free();
    }
}

registerClass('Color');

$colors = [
    'Black' => [0, 0, 0],
    'AlmostBlack' => [48, 48, 48],
    'VeryDarkGray' => [88, 88, 88],
    'DarkGray' => [128, 128, 128],
    'MidGray' => [160, 160, 160],
    'LightGray' => [195, 195, 195],
    'VeryLightGray' => [220, 220, 220],
    'White' => [255, 255, 255],
    'VeryDarkRed' => [64, 0, 0],
    'DarkRed' => [128, 0, 0],
    'MidRed' => [192, 0, 0],
    'Red' => [255, 0, 0],
    'LightRed' => [255, 192, 192],
    'VeryDarkGreen' => [0, 64, 0],
    'DarkGreen' => [0, 128, 0],
    'MidGreen' => [0, 192, 0],
    'Green' => [0, 255, 0],
    'LightGreen' => [192, 255, 192],
    'VeryDarkBlue' => [0, 0, 64],
    'DarkBlue' => [0, 0, 128],
    'MidBlue' => [0, 0, 192],
    'Blue' => [0, 0, 255],
    'LightBlue' => [192, 192, 255],
    'VeryDarkYellow' => [64, 64, 0],
    'DarkYellow' => [128, 128, 0],
    'MidYellow' => [192, 192, 0],
    'Yellow' => [255, 255, 2],
    'LightYellow' => [255, 255, 192],
    'VeryDarkCyan' => [0, 64, 64],
    'DarkCyan' => [0, 128, 128],
    'MidCyan' => [0, 192, 192],
    'Cyan' => [0, 255, 255],
    'LightCyan' => [192, 255, 255],
    'VeryDarkMagenta' => [64, 0, 64],
    'DarkMagenta' => [128, 0, 128],
    'MidMagenta' => [192, 0, 192],
    'Magenta' => [255, 0, 255],
    'LightMagenta' => [255, 192, 255],
    'DarkOrange' => [192, 88, 0],
    'Orange' => [255, 128, 0],
    'LightOrange' => [255, 168, 88],
    'VeryLightOrange' => [255, 220, 168],
    'DarkPink' => [192, 0, 88],
    'Pink' => [255, 0, 128],
    'LightPink' => [255, 88, 168],
    'VeryLightPink' => [255, 168, 220],
    'DarkPurple' => [88, 0, 192],
    'Purple' => [128, 0, 255],
    'LightPurple' => [168, 88, 255],
    'VeryLightPurple' => [220, 168, 255],
];



$php = '';

foreach ($colors as $name => $color) {
    [$red, $green, $blue] = $color;

    $php .= '
	class aw' . $name . ' extends awColor {

		public function __construct($alpha = 0) {
			parent::__construct(' . $red . ', ' . $green . ', ' . $blue . ', $alpha);
		}

	}
	';

    if (ARTICHOW_PREFIX !== 'aw') {
        $php .= '
		class ' . ARTICHOW_PREFIX . $name . ' extends aw' . $name . ' {

		}
		';
    }
}

eval($php);
