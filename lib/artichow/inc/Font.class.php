<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */


/**
 * Built-in PHP fonts
 *
 * @package Artichow
 */
class awFont {

	/**
	 * Used font
	 *
	 * @param int $font
	 */
	public $font;

	/**
	 * Build the font
	 *
	 * @param int $font Font identifier
	 */
	public function __construct($font) {

		$this->font = $font;

	}

	/**
	 * Draw a text
	 *
	 * @param awDrawer $drawer
	 * @param awPoint $p Draw text at this point
	 * @param awText $text The text
	 */
	public function draw(awDrawer $drawer, awPoint $p, awText $text) {

		$angle = $text->getAngle();

		if($angle !== 90 and $angle !== 0) {
			trigger_error("You can only use 0° and 90°", E_USER_ERROR);
		}

		if($angle === 90) {
			$function = 'imagestringup';
		} else {
			$function = 'imagestring';
		}

		if($angle === 90) {
			$add = $this->getTextHeight($text);
		} else {
			$add = 0;
		}

		$color = $text->getColor();
		$rgb = $color->getColor($drawer->resource);


	    $function(
			$drawer->resource,
		    $this->font,
    		$drawer->x + $p->x,
		    $drawer->y + $p->y + $add,
		    $text->getText(),
			$rgb
	    );

	}

	/**
	 * Get the width of a string
	 *
	 * @param awText $text A string
	 */
	public function getTextWidth(awText $text) {

		if($text->getAngle() === 90) {
			$text->setAngle(45);
			return $this->getTextHeight($text);
		} else if($text->getAngle() === 45) {
			$text->setAngle(90);
		}

		$font = $text->getFont();
		$fontWidth = imagefontwidth($font->font);

		if($fontWidth === FALSE) {
			trigger_error("Unable to get font size", E_USER_ERROR);
		}

		return (int)$fontWidth * strlen($text->getText());

	}

	/**
	 * Get the height of a string
	 *
	 * @param awText $text A string
	 */
	public function getTextHeight(awText $text) {

		if($text->getAngle() === 90) {
			$text->setAngle(45);
			return $this->getTextWidth($text);
		} else if($text->getAngle() === 45) {
			$text->setAngle(90);
		}

		$font = $text->getFont();
		$fontHeight = imagefontheight($font->font);

		if($fontHeight === FALSE) {
			trigger_error("Unable to get font size", E_USER_ERROR);
		}

		return (int)$fontHeight;

	}

}

registerClass('Font');

/**
 * TTF fonts
 *
 * @package Artichow
 */
class awTTFFont extends awFont {

	/**
	 * Font size
	 *
	 * @var int
	 */
	public $size;

	/**
	 * Font file
	 *
	 * @param string $font Font file
	 * @param int $size Font size
	 */
	public function __construct($font, $size) {

		parent::__construct($font);

		$this->size = (int)$size;

	}

	/**
	 * Draw a text
	 *
	 * @param awDrawer $drawer
	 * @param awPoint $p Draw text at this point
	 * @param awText $text The text
	 */
	public function draw(awDrawer $drawer, awPoint $p, awText $text) {

		// Make easier font positionment
		$textWorking = $text->getText();
		$offsetTop = 0;
		$offsetRight = 0;

		/* Ugly font repositioning hack. FIXME:  Make me not ugly.*/
  		if(strpos($textWorking, "||") === 0)
   		{
       		$textWorking = substr($textWorking, 2);
       		$offsetTop += 10;
       		$offsetRight += 2;
   		}
  		else if(strpos($textWorking, "|") === 0)
   		{
       		$textWorking = substr($textWorking, 1);
       		$offsetTop += 12;
       		$offsetRight += 2;
   		}

  		else if(strpos($textWorking, "}") === 0)
   		{
       		$textWorking = substr($textWorking, 1);
       		$offsetTop += 3;
       		$offsetRight -= 7;
   		}
   		
		$textParts = explode("\n", $textWorking);
		$textFirstPart = $textParts[0];
   		
		$text->setText($textWorking." ");

		$color = $text->getColor();
		$rgb = $color->getColor($drawer->resource);

		$box = imagettfbbox($this->size, $text->getAngle(), $this->font, $textWorking);

		$height =  - $box[5];

		$box = imagettfbbox($this->size, 90, $this->font, $textWorking);
		$width = abs($box[6] - $box[2]);

		// Restore old text
		$text->setText(substr($textWorking, 0, strlen($textWorking) - 1));

		do
		{
    		if(strpos($textWorking, "\n") !== false)
    		{
        		$theText = substr($textWorking, 0, strpos($textWorking, "\n"));
        		$textWorking = substr($textWorking, strpos($textWorking, "\n") + 1);
    		}
    		else
    		{
        		$theText = $textWorking;
        		$textWorking = '';
    		}

    		if($theText != '')
    		{
    		    imagettftext(
    		    	$drawer->resource,
    	    		$this->size,
        			$text->getAngle(),
    			    $drawer->x + $p->x + $width  * sin($text->getAngle() / 180 * M_PI) + $offsetRight,
        			$drawer->y + $p->y + $height + $offsetTop,
    			    $rgb,
    			    $this->font,
        			$theText
    		    );
 		    }
      		$offsetTop += 15;
	    } while ($textWorking != '');

	}

	/**
	 * Get the width of a string
	 *
	 * @param awText $text A string
	 */
	public function getTextWidth(awText $text) {

        $theText = $text->getText();
        $theTextParts = explode("\n", $theText);
        $theTextFirstPart = $theTextParts[0];

		$box = imagettfbbox($this->size, $text->getAngle(), $this->font, $theTextFirstPart);

		if($box === FALSE) {
			trigger_error("Unable to get font size", E_USER_ERROR);
			return;
		}

		list(, , $x2, $y2, , , $x1, $y1) = $box;

		return abs($x2 - $x1);

	}

	/**
	 * Get the height of a string
	 *
	 * @param awText $text A string
	 */
	public function getTextHeight(awText $text) {

		$box = imagettfbbox($this->size, $text->getAngle(), $this->font, $text->getText());

		if($box === FALSE) {
			trigger_error("Unable to get font size", E_USER_ERROR);
			return;
		}

		list(, , $x2, $y2, , , $x1, $y1) = $box;

		return abs($y2 - $y1);

	}

}

registerClass('TTFFont');



$php = '';

for($i = 1; $i <= 5; $i++) {

	$php .= '
	class awFont'.$i.' extends awFont {

		public function __construct() {
			parent::__construct('.$i.');
		}

	}
	';

	if(ARTICHOW_PREFIX !== 'aw') {
		$php .= '
		class '.ARTICHOW_PREFIX.'Font'.$i.' extends awFont'.$i.' {
		}
		';
	}

}

eval($php);

$php = '';

foreach($fonts as $font) {

	$php .= '
	class aw'.$font.' extends awTTFFont {

		public function __construct($size) {
			parent::__construct(\''.(ARTICHOW_FONT.DIRECTORY_SEPARATOR.$font.'.ttf').'\', $size);
		}

	}
	';

	if(ARTICHOW_PREFIX !== 'aw') {
		$php .= '
		class '.ARTICHOW_PREFIX.$font.' extends aw'.$font.' {
		}
		';
	}

}

eval($php);



?>
