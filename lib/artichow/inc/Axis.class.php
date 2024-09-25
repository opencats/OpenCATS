<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */

/**
 * Handle axis
 *
 * @package Artichow
 */
class awAxis
{
    /**
     * Axis line
     *
     * @var Line
     */
    public $line;

    /**
     * Axis labels
     *
     * @var Label
     */
    public $label;

    /**
     * Axis title
     *
     * @var Label
     */
    public $title;

    /**
     * Title position
     *
     * @var float
     */
    protected $titlePosition = 0.5;

    /**
     * Labels number
     *
     * @var int
     */
    protected $labelNumber;

    /**
     * Axis ticks
     *
     * @var array
     */
    protected $ticks = [];

    /**
     * Axis and ticks color
     *
     * @var Color
     */
    protected $color;

    /**
     * Axis left and right padding
     *
     * @var Side
     */
    protected $padding;

    /**
     * Axis range
     *
     * @var array
     */
    protected $range;

    /**
     * Hide axis
     *
     * @var bool
     */
    protected $hide = false;

    /**
     * Auto-scaling mode
     *
     * @var bool
     */
    protected $auto = true;

    public $forcedMax = null;

    public $dashboardImageMode = false;

    /**
     * Axis range callback function
     *
     * @var array
     */
    protected $rangeCallback = [
        'toValue' => 'toProportionalValue',
        'toPosition' => 'toProportionalPosition',
    ];

    /**
     * Build the axis
     *
     * @param float $min Begin of the range of the axis
     * @param float $max End of the range of the axis
     */
    public function __construct($min = null, $max = null)
    {
        $this->line = new awVector(
            new awPoint(0, 0),
            new awPoint(0, 0)
        );

        $this->label = new awLabel();
        $this->padding = new awSide();

        $this->title = new awLabel(
            null,
            null,
            null,
            0
        );

        $this->setColor(new awBlack());

        if ($min !== null and $max !== null) {
            $this->setRange($min, $max);
        }
    }

    /**
     * Enable/disable auto-scaling mode
     *
     * @param bool $auto
     */
    public function auto($auto)
    {
        $this->auto = (bool) $auto;
    }

    /**
     * Enable/disable auto-scaling mode
     */
    public function setDashboardImageMode($mode)
    {
        $this->dashboardImageMode = (bool) $mode;
    }

    /**
     * Get auto-scaling mode status
     *
     * @return bool
     */
    public function isAuto()
    {
        return $this->auto;
    }

    /**
     * Hide axis
     *
     * @param bool $hide
     */
    public function hide($hide = true)
    {
        $this->hide = (bool) $hide;
    }

    /**
     * Show axis
     *
     * @param bool $show
     */
    public function show($show = true)
    {
        $this->hide = ! (bool) $show;
    }

    /**
     * Return a tick object from its name
     *
     * @param string $name Tick object name
     * @return Tick
     */
    public function tick($name)
    {
        return array_key_exists($name, $this->ticks) ? $this->ticks[$name] : null;
    }

    /**
     * Add a tick object
     *
     * @param string $name Tick object name
     * @param awTick $tick Tick object
     */
    public function addTick($name, awTick $tick)
    {
        $this->ticks[$name] = $tick;
    }

    /**
     * Delete a tick object
     *
     * @param string $name Tick object name
     */
    public function deleteTick($name)
    {
        if (array_key_exists($name, $this->ticks)) {
            unset($this->ticks[$name]);
        }
    }

    /**
     * Hide all ticks
     *
     * @param bool $hide Hide or not ?
     */
    public function hideTicks($hide = true)
    {
        foreach ($this->ticks as $tick) {
            $tick->hide($hide);
        }
    }

    /**
     * Change ticks style
     *
     * @param int $style Ticks style
     */
    public function setTickStyle($style)
    {
        foreach ($this->ticks as $tick) {
            $tick->setStyle($style);
        }
    }

    /**
     * Change ticks interval
     *
     * @param int $interval Ticks interval
     */
    public function setTickInterval($interval)
    {
        foreach ($this->ticks as $tick) {
            $tick->setInterval($interval);
        }
    }

    /**
     * Change number of ticks relative to others ticks
     *
     * @param awTick $to Change number of theses ticks
     * @param awTick $from Ticks reference
     * @param float $number Number of ticks by the reference
     */
    public function setNumberByTick($to, $from, $number)
    {
        $this->ticks[$to]->setNumberByTick($this->ticks[$from], $number);
    }

    /**
     * Reverse ticks style
     */
    public function reverseTickStyle()
    {
        foreach ($this->ticks as $tick) {
            if ($tick->getStyle() === awTick::IN) {
                $tick->setStyle(awTick::OUT);
            } elseif ($tick->getStyle() === awTick::OUT) {
                $tick->setStyle(awTick::IN);
            }
        }
    }

    /**
     * Change interval of labels
     *
     * @param int $interval Interval
     */
    public function setLabelInterval($interval)
    {
        $this->auto(false);
        $this->setTickInterval($interval);
        $this->label->setInterval($interval);
    }

    /**
     * Change number of labels
     *
     * @param int $number Number of labels to display (can be NULL)
     */
    public function setLabelNumber($number)
    {
        $this->auto(false);
        $this->labelNumber = is_null($number) ? null : (int) $number;
    }

    /**
     * Get number of labels
     *
     * @return int
     */
    public function getLabelNumber()
    {
        return $this->labelNumber;
    }

    /**
     * Change precision of labels
     *
     * @param int $precision Precision
     */
    public function setLabelPrecision($precision)
    {
        $this->auto(false);
        $this->label->setCallbackFunction(fn ($value) => sprintf('%.' . (int) $precision . 'f', $value));
    }

    /**
     * Change text of labels
     *
     * @param array $texts Some texts
     */
    public function setLabelText($texts)
    {
        if (is_array($texts)) {
            $this->auto(false);
            $this->label->setCallbackFunction(fn ($value) => $texts[$value] ?? '?');
        }
    }

    /**
     * Get the position of a point
     *
     * @param awAxis $xAxis X axis
     * @param awAxis $yAxis Y axis
     * @param awPoint $p Position of the point
     * @return Point Position on the axis
     */
    public static function toPosition(awAxis $xAxis, awAxis $yAxis, awPoint $p)
    {
        $p1 = $xAxis->getPointFromValue($p->x);
        $p2 = $yAxis->getPointFromValue($p->y);

        return new awPoint(
            round($p1->x),
            round($p2->y)
        );
    }

    /**
     * Change title alignment
     *
     * @param int $alignment New Alignment
     */
    public function setTitleAlignment($alignment)
    {
        switch ($alignment) {
            case awLabel::TOP:
                $this->setTitlePosition(1);
                $this->title->setAlign(null, awLabel::BOTTOM);
                break;

            case awLabel::BOTTOM:
                $this->setTitlePosition(0);
                $this->title->setAlign(null, awLabel::TOP);
                break;

            case awLabel::LEFT:
                $this->setTitlePosition(0);
                $this->title->setAlign(awLabel::LEFT);
                break;

            case awLabel::RIGHT:
                $this->setTitlePosition(1);
                $this->title->setAlign(awLabel::RIGHT);
                break;
        }
    }

    /**
     * Change title position on the axis
     *
     * @param float $position A new awposition between 0 and 1
     */
    public function setTitlePosition($position)
    {
        $this->titlePosition = (float) $position;
    }

    /**
     * Change axis and axis title color
     */
    public function setColor(awColor $color)
    {
        $this->color = $color;
        $this->title->setColor($color);
    }

    /**
     * Change axis padding
     *
     * @param int $left Left padding in pixels
     * @param int $right Right padding in pixels
     */
    public function setPadding($left, $right)
    {
        $this->padding->set($left, $right);
    }

    /**
     * Get axis padding
     *
     * @return Side
     */
    public function getPadding()
    {
        return $this->padding;
    }

    /**
     * Change axis range
     *
     * @param float $min
     * @param float $max
     */
    public function setRange($min, $max)
    {
        if ($min !== null) {
            $this->range[0] = (float) $min;
        }
        if ($max !== null) {
            $this->range[1] = (float) $max;
        }
    }

    /**
     * Get axis range
     *
     * @return array
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Change axis range callback function
     *
     * @param string $toValue Transform a position between 0 and 1 to a value
     * @param string $toPosition Transform a value to a position between 0 and 1 on the axis
     */
    public function setRangeCallback($toValue, $toPosition)
    {
        $this->rangeCallback = [
            'toValue' => (string) $toValue,
            'toPosition' => (string) $toPosition,
        ];
    }

    /**
     * Center X values of the axis
     *
     * @param awAxis $axis An axis
     * @param float $value The reference value on the axis
     */
    public function setXCenter(awAxis $axis, $value)
    {
        // Check vector angle
        if ($this->line->isVertical() === false) {
            trigger_error("setXCenter() can only be used on vertical axes", E_USER_ERROR);
        }

        $p = $axis->getPointFromValue($value);

        $this->line->setX(
            $p->x,
            $p->x
        );
    }

    /**
     * Center Y values of the axis
     *
     * @param awAxis $axis An axis
     * @param float $value The reference value on the axis
     */
    public function setYCenter(awAxis $axis, $value)
    {
        // Check vector angle
        if ($this->line->isHorizontal() === false) {
            trigger_error("setYCenter() can only be used on horizontal axes", E_USER_ERROR);
        }

        $p = $axis->getPointFromValue($value);

        $this->line->setY(
            $p->y,
            $p->y
        );
    }

    /**
     * Get the distance between to values on the axis
     *
     * @param float $from The first value
     * @param float $to The last value
     * @return Point
     */
    public function getDistance($from, $to)
    {
        $p1 = $this->getPointFromValue($from);
        $p2 = $this->getPointFromValue($to);

        return $p1->getDistance($p2);
    }

    /**
     * Get a point on the axis from a value
     *
     * @param float $value
     * @return Point
     */
    protected function getPointFromValue($value)
    {
        $callback = $this->rangeCallback['toPosition'];

        [$min, $max] = $this->range;
        if ($this->forcedMax !== null && $this->forcedMax > $max) {
            $max = $this->forcedMax;
        }
        $position = $callback($value, $min, $max);

        return $this->getPointFromPosition($position);
    }

    /**
     * Get a point on the axis from a position
     *
     * @param float $position A position between 0 and 1
     * @return Point
     */
    protected function getPointFromPosition($position)
    {
        $vector = $this->getVector();

        $angle = $vector->getAngle();
        $size = $vector->getSize();

        return $vector->p1->move(
            cos($angle) * $size * $position,
            -1 * sin($angle) * $size * $position
        );
    }

    /**
     * Draw axis
     *
     * @param awDrawer $drawer A drawer
     */
    public function draw(awDrawer $drawer)
    {
        if ($this->hide) {
            return;
        }

        $vector = $this->getVector();

        // Draw axis ticks
        $this->drawTicks($drawer, $vector);

        // Draw axis line
        $this->line($drawer);

        // Draw labels
        $this->drawLabels($drawer);

        // Draw axis title
        $p = $this->getPointFromPosition($this->titlePosition);
        $this->title->draw($drawer, $p);
    }

    public function autoScale()
    {
        if ($this->isAuto() === false) {
            return;
        }

        [$min, $max] = $this->getRange();
        $interval = $max - $min;

        if ($interval < 1) {
            $interval = 1;
        }

        $partMax = $max / $interval;
        $partMin = $min / $interval;

        $difference = log($interval) / log(10);
        $difference = floor($difference);

        $pow = 10 ** $difference;

        $intervalNormalize = $interval / $pow;

        if ($difference <= 0) {
            $precision = $difference * -1 + 1;

            if ($intervalNormalize > 2) {
                $precision--;
            }
        } else {
            $precision = 0;
        }

        if ($min != 0 and $max != 0) {
            $precision++;
        }


        $this->setLabelPrecision($precision);

        if ($intervalNormalize <= 1.5) {
            $intervalReal = 1.5;
            $labelNumber = 4;
        } elseif ($intervalNormalize <= 2) {
            $intervalReal = 2;
            $labelNumber = 5;
        } elseif ($intervalNormalize <= 3) {
            $intervalReal = 3;
            $labelNumber = 4;
        } elseif ($intervalNormalize <= 4) {
            $intervalReal = 4;
            $labelNumber = 5;
        } elseif ($intervalNormalize <= 5) {
            $intervalReal = 5;
            $labelNumber = 6;
        } elseif ($intervalNormalize <= 8) {
            $intervalReal = 8;
            $labelNumber = 5;
        } elseif ($intervalNormalize <= 10) {
            $intervalReal = 10;
            $labelNumber = 6;
        }

        if ($min == 0) {
            $this->setRange(
                $min,
                $intervalReal * $pow
            );
        } elseif ($max == 0) {
            $this->setRange(
                $intervalReal * $pow * -1,
                0
            );
        }

        $this->setLabelNumber($labelNumber);
    }

    protected function line(awDrawer $drawer)
    {
        $drawer->line(
            $this->color,
            $this->line
        );
    }

    protected function drawTicks(awDrawer $drawer, awVector $vector)
    {
        foreach ($this->ticks as $tick) {
            $tick->setColor($this->color);
            $tick->draw($drawer, $vector, $this->dashboardImageMode);
        }
    }

    protected function drawLabels($drawer)
    {
        if ($this->labelNumber !== null) {
            [$min, $max] = $this->range;
            $number = $this->labelNumber - 1;
            if ($number < 1) {
                return;
            }
            $function = $this->rangeCallback['toValue'];
            $labels = [];
            for ($i = 0; $i <= $number; $i++) {
                $labels[] = $function($i / $number, $min, $max);
            }
            $this->label->set($labels);
        }

        $labels = $this->label->count();

        for ($i = 0; $i < $labels; $i++) {
            if ($this->label->get($i) / 2 == (int) $this->label->get($i) / 2) {
                $p = $this->getPointFromValue($this->label->get($i));
                $this->label->draw($drawer, $p, $i);
            }
        }
    }

    protected function getVector()
    {
        $angle = $this->line->getAngle();

        // Compute paddings
        $vector = new awVector(
            $this->line->p1->move(
                cos($angle) * $this->padding->left,
                -1 * sin($angle) * $this->padding->left
            ),
            $this->line->p2->move(
                -1 * cos($angle) * $this->padding->right,
                -1 * -1 * sin($angle) * $this->padding->right
            )
        );

        return $vector;
    }

    public function __clone()
    {
        $this->label = clone $this->label;
        $this->line = clone $this->line;
        $this->title = clone $this->title;

        foreach ($this->ticks as $name => $tick) {
            $this->ticks[$name] = clone $tick;
        }
    }
}

registerClass('Axis');

function toProportionalValue($position, $min, $max)
{
    return $min + ($max - $min) * $position;
}

function toProportionalPosition($value, $min, $max)
{
    if ($max - $min == 0) {
        return 0;
    }
    return ($value - $min) / ($max - $min);
}
