<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */

require_once dirname(__FILE__)."/Plot.class.php";

/**
 * BarPlot
 *
 * @package Artichow
 */
class awBarPlotDashboard extends awPlot implements awLegendable {

	/**
	 * Labels on your bar plot
	 *
	 * @var Label
	 */
	public $label;

	/**
	 * Bar plot identifier
	 *
	 * @var int
	 */
	protected $identifier;

	/**
	 * Bar plot number
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * Bar plot depth
	 *
	 * @var int
	 */
	protected $depth;

	/**
	 * For moving bars
	 *
	 * @var int
	 */
	protected $move;

	/**
	 * Bars shadow
	 *
	 * @var Shadow
	 */
	public $barShadow;

	/**
	 * Bars border
	 *
	 * @var Border
	 */
	public $barBorder;

	/**
	 * Bars padding
	 *
	 * @var Side
	 */
	protected $barPadding;

	/**
	 * Bars space
	 *
	 * @var int
	 */
	protected $barSpace = 0;

	/**
	 * Bars background
	 *
	 * @var Color, Gradient
	 */
	protected $barBackground;

	protected $onRowNumber;
	public $arrayBarBackground;
	public $maxValue;
	public $drawPercent;
	public $legendColorCounter;
	public $noData;

	/**
	 * Construct a new awBarPlot
	 *
	 * @param array $values Some numeric values for Y axis
	 * @param int $identifier Plot identifier
	 * @param int $number Bar plot number
	 * @param int $depth Bar plot depth in pixels
	 */
	public function __construct($values, $identifier = 1, $number = 1, $depth = 0, $maxValue, $drawPercent = true, $noData = false) {

		parent::__construct();

		$this->label = new awLabel;
		$this->onRowNumber = 0;
		$this->arrayBarBackground = 0;
		$this->legendColorCounter = 0;

		$this->barPadding = new awSide(0.06, 0.06, 0, 0);
		$this->barShadow = new awShadow(awShadow::RIGHT_TOP);
		$this->barBorder = new awBorder;

        $maxValue=10;
        foreach ($values as $value)
        {
            if ($value > $maxValue)
            {
                $maxValue = $value;
            }
        }

		$this->setValues($values);
		
		$this->setXMax($maxValue);
		$this->maxValue = $maxValue;
		$this->yAxis->forcedMax = $maxValue;

		$this->identifier = (int)$identifier;
		$this->number = (int)$number;
		$this->depth = (int)$depth;

		$this->move = new awSide;

		$this->drawPercent = $drawPercent;
		$this->noData = $noData;

		if(!$this->drawPercent)
		{
    		$GLOBALS['jpegGraph'] = true;
		}

		// Hide vertical grid
		$this->grid->hideVertical(TRUE);

	}

	/**
	 * Change bars padding
	 * This method is not compatible with awBarPlot::setBarPadding()
	 *
	 * @param float $left Left padding (between 0 and 1)
	 * @param float $right Right padding (between 0 and 1)
	 */
	public function setBarPadding($left = NULL, $right = NULL) {
		$this->barPadding->set($left, $right);
	}

	/**
	 * Change bars size
	 * This method is not compatible with awBarPlot::setBarPadding()
	 *
	 * @param int $width Bars size (between 0 and 1)
	 */
	public function setBarSize($size) {
		$padding = (1 - $size) / 2;
		$this->barPadding->set($padding, $padding);
	}

	/**
	 * Move bars
	 *
	 * @param int $x
	 * @param int $y
	 */
	public function move($x, $y) {
		$this->move->set($x, NULL, $y, NULL);
	}

	/**
	 * Change bars space
	 *
	 * @param int $space Space in pixels
	 */
	public function setBarSpace($space) {
		$this->barSpace = (int)$space;
	}

	/**
	 * Change line background color
	 *
	 * @param awColor $color
	 */
	public function setBarColor(awColor $color) {
		$this->barBackground = $color;
	}

	/**
	 * Change line background gradient
	 *
	 * @param awGradient $gradient
	 */
	public function setBarGradient(awGradient $gradient) {
		$this->barBackground = $gradient;
	}

	/**
	 * Get the line thickness
	 *
	 * @return int
	 */
	public function getLegendLineThickness() {
	}

	/**
	 * Get the line type
	 *
	 * @return int
	 */
	public function getLegendLineStyle() {
	}

	/**
	 * Get the color of line
	 *
	 * @return Color
	 */
	public function getLegendLineColor() {
	}

	/**
	 * Get the background color or gradient of an element of the component
	 *
	 * @return Color, Gradient
	 */
	public function getLegendBackground() {
	    // This is a hack.
        $color = $this->arrayBarBackground[(int) $this->legendColorCounter];
        $this->legendColorCounter += .5;  //This is called twice per bar for some reason.
		return $color;
	}

	/**
	 * Get a mark object
	 *
	 * @return Mark
	 */
	public function getLegendMark() {
	}

	public function drawComponent(awDrawer $drawer, $x1, $y1, $x2, $y2, $aliasing) {
		$this->label->setFont(new Tuffy(8));
    	
    	$datayReal = $this->datay;
    	$dataySkewed = array();

    	for($index = 0; $index < count($this->datay); $index++)
    	{
           	$dataySkewed[$index] = false;
       	}

		$count = count($this->datay);
		$max = $this->getRealYMax(NULL);
		$min = $this->getRealYMin(NULL);


		// Find zero for bars
		if($this->xAxisZero and $min <= 0 and $max >= 0) {
			$zero = 0;
		} else if($max < 0) {
			$zero = $max;
		} else {
			$zero = $min;
		}

		// Get base position
		$zero = awAxis::toPosition($this->xAxis, $this->yAxis, new awPoint(0, $zero));

		// Distance between two values on the graph
		$distance = $this->xAxis->getDistance(0, 1);

		// Compute paddings
		$leftPadding = $this->barPadding->left * $distance;
		$rightPadding = $this->barPadding->right * $distance;

		$padding = $leftPadding + $rightPadding;
		$space = $this->barSpace * ($this->number - 1);

		$barSize = ($distance - $padding - $space) / $this->number;
		$barPosition = $leftPadding + $barSize * ($this->identifier - 1);

		for($key = 0; $key < $count; $key++) {

			$value = $this->datay[$key];

			if($value !== NULL) {

    			/* Determine if we should use cut symbol */


				$position = awAxis::toPosition(
					$this->xAxis,
					$this->yAxis,
					new awPoint($key, $value)
				);

				$barStart = $barPosition + ($this->identifier - 1) * $this->barSpace + $position->x;
				$barStop = $barStart + $barSize;

				$t1 = min($zero->y, $position->y);
				$t2 = max($zero->y, $position->y);

				if(round($t2 - $t1) == 0) {
					continue;
				}

				if ($dataySkewed[$key])
				{
    				$xmin = round($t1) - $this->depth + $this->move->top;
    				$xmax = round($t2) - $this->depth + $this->move->top;

    				$dividerPoint = (($xmax - $xmin) * 0.2) + $xmin;

                    $p1x = round($barStart) + $this->depth + $this->move->left;
                    $p1y = round($t1) - $this->depth + $this->move->top;

                    $p2x = round($barStop) + $this->depth + $this->move->left;
                    $p2y = round($t2) - $this->depth + $this->move->top;

    				$p1 = new awPoint($p1x, $p1y);
    				$p2 = new awPoint($p2x, $p2y);

    				$this->drawBar($drawer, $p1, $p2, $key);

                    $lineX = round($barStop) + $this->depth + $this->move->left;
                    $lineY = $dividerPoint;

                    /* Draw a two-pixel white line over the bar at the divider point. */
                    $white = imagecolorallocate($drawer->resource, 255, 255, 255);
                    imageline($drawer->resource, $lineX - $barSize + 1, $lineY, $lineX + 1, $lineY, $white);
                    imageline($drawer->resource, $lineX - $barSize + 1, $lineY + 1, $lineX + 1, $lineY + 1, $white);
				}
				else
				{
    				$p1 = new awPoint(
    					round($barStart) + $this->depth + $this->move->left,
    					round($t1) - $this->depth + $this->move->top
    				);

    				$p2 = new awPoint(
    					round($barStop) + $this->depth + $this->move->left,
    					round($t2) - $this->depth + $this->move->top
    				);

    				$this->drawBar($drawer, $p1, $p2, $key);
				}

			}

		}

		$maxValue = $this->maxValue;

		// Draw labels
		foreach($datayReal as $key => $value) {

			if($value !== NULL) {

    			if ($value > $maxValue)
    			{
        			$maxValue = $value;
    			}

				$position = awAxis::toPosition(
					$this->xAxis,
					$this->yAxis,
					new awPoint($key, $this->datay[$key])
				);

				$position2 = awAxis::toPosition(
					$this->xAxis,
					$this->yAxis,
					new awPoint($key, $this->datay[$key] / 2)
				);

				$point = new awPoint(
					$barPosition + ($this->identifier - 1) * $this->barSpace + $position->x + $barSize / 2 + 1 + $this->depth,
					$position->y - $this->depth
				);

				$point2 = new awPoint(
					$barPosition + ($this->identifier - 1) * $this->barSpace + $position2->x + $barSize / 2 + 1 + $this->depth,
					$position2->y - $this->depth
				);

				if($maxValue == 0)
				{
    				$maxValue = 1;
				}

				if($value != 0 && $this->drawPercent)
				{
				    /* Mode 2 is the white box number at the top of the bar */
				    $this->label->drawSpecial($drawer, $point2, $key, $value);
				    //$this->label->draw($drawer, $point, $key);
			    }
			}
		}

		switch ($this->view)
		{
		    case DASHBOARD_GRAPH_YEARLY:
    		    $tabImage = imagecreatefrompng('images/tab3.png');
    		    imagecopy($drawer->resource, $tabImage, 399, 0, 0, 0, 63, 73);
    		    break;
    		    		    
		    case DASHBOARD_GRAPH_MONTHLY:
    		    $tabImage = imagecreatefrompng('images/tab2.png');
    		    imagecopy($drawer->resource, $tabImage, 399, 0, 0, 0, 63, 73);
    		    break;
    				    
		    case DASHBOARD_GRAPH_WEEKLY:
		    default:
    		    $tabImage = imagecreatefrompng('images/tab1.png');
    		    imagecopy($drawer->resource, $tabImage, 399, 0, 0, 0, 63, 73);
    		    break;
	    }
	    
	    if ($this->noData)
	    {
		    $noDataImage = imagecreatefromjpeg('images/graphNoData.jpg');
		    imagecopy($drawer->resource, $noDataImage, 27, 13, 0, 0, 363, 195);	        
        }
	}

	public function getXAxisNumber() {
		return count($this->datay) + 1;
	}
	// ça bidouille à fond ici !
	public function getXMax() {
        	return array_max($this->datax) + 1;
	}

	public function getXCenter() {
		return TRUE;
	}

	protected function drawBar(awDrawer $drawer, awPoint $p1, awPoint $p2, $key) {

		// Draw shadow
		$this->barShadow->draw(
			$drawer,
			$p1,
			$p2,
			awShadow::OUT
		);

		if(abs($p2->y - $p1->y) > 1) {

			$this->barBorder->rectangle(
				$drawer,
				$p1,
				$p2
			);

			if($this->barBackground !== NULL) {

				$size = $this->barBorder->visible() ? 1 : 0;

				$b1 = $p1->move($size, $size);
				$b2 = $p2->move(-1 * $size, -1 * $size);

				// Draw background
				$drawer->filledRectangle(
					$this->arrayBarBackground[$key],
					new awLine($b1, $b2)
				);

			}

		}
	}

}

registerClass('BarPlotDashboard');
?>
