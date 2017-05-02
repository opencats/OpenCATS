<?php
/**
 * CATS
 * Graph Generation Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: GraphGenerator.php 3705 2007-11-26 23:34:51Z will $
 */

define('GRAPH_TREND_LINES', false);

/* Is GD2 installed? */
if (function_exists('ImageCreateFromJpeg'))
{
    include_once(LEGACY_ROOT . '/lib/artichow/LinePlot.class.php');
    include_once(LEGACY_ROOT . '/lib/artichow/BarPlot.class.php');
    include_once(LEGACY_ROOT . '/lib/artichow/inc/Label.class.php');
    include_once(LEGACY_ROOT . '/lib/artichow/BarPlotPipeline.class.php');
    include_once(LEGACY_ROOT . '/lib/artichow/BarPlotDashboard.class.php');
    include_once(LEGACY_ROOT . '/lib/artichow/AntiSpam.class.php');
    include_once(LEGACY_ROOT . '/lib/artichow/Pie.class.php');
}

/**
 *	Simple Graph Generator
 *	@package    CATS
 *	@subpackage Library
 */
class GraphSimple
{
    private $xLabels;
    private $xValues;
    private $color;
    private $title;

    public function __construct($xLabels, $xValues, $color, $title, $width, $height)
    {
        $this->xLabels = $xLabels;
        $this->xValues = $xValues;
        $this->color = $color;
        $this->title = $title;
        $this->width = $width;
        $this->height = $height;
    }

    // FIXME: Document me.
    public function draw($format = false)
    {
        /* Make sure we have GD support. */
        if (!function_exists('imagecreatefromjpeg'))
        {
            die();
        }

        if ($format === false)
        {
            $format = IMG_PNG;
        }

        $group = new PlotGroup();
        $graph = new Graph($this->width, $this->height);

        $graph->setFormat($format);
        $graph->setBackgroundColor(new Color(0xF4, 0xF4, 0xF4));
        $graph->shadow->setSize(3);

        $graph->title->set($this->title);
        $graph->title->setFont(new Tuffy(10));
        $graph->title->setColor(new Color(0x00, 0x00, 0x8B));
        $graph->border->setColor(new Color(187, 187, 187, 15));

        $plot = new BarPlot($this->xValues);
        $plot->setBarColor(new $this->color);
        $plot->barBorder->hide(true);
        $plot->setBarGradient(new LinearGradient(new $this->color, new White, 0));
        $plot->setBarPadding(0.2, 0.2);

        $group->axis->bottom->setLabelText($this->xLabels);
        $group->axis->bottom->label->setFont(new Tuffy(8));

        $plot2 = new LinePlot($this->xValues, LinePlot::MIDDLE);
        $plot2->setColor(new DarkBlue);
        $plot2->setThickness(1);

        if (GRAPH_TREND_LINES)
        {
            $group->add($plot2);
        }

        $group->add($plot);

        $graph->add($group);

        $graph->draw();
    }
}

/**
 *	Simple Pie Graph Generator
 *	@package    CATS
 *	@subpackage Library
 */
class GraphPie
{
    private $xLabels;
    private $xValues;
    private $title;

    public function __construct($xLabels, $xValues, $title, $width, $height)
    {
        $this->xLabels = $xLabels;
        $this->xValues = $xValues;
        $this->title = $title;
        $this->width = $width;
        $this->height = $height;
    }

    // FIXME: Document me.
    public function draw($format = false)
    {
        /* Make sure we have GD support. */
        if (!function_exists('imagecreatefromjpeg'))
        {
            die();
        }

        if ($format === false)
        {
            $format = IMG_PNG;
        }

        $graph = new Graph($this->width, $this->height);

        $colors = array (
                new Green,
                new Orange
            );

        $graph->setFormat($format);
        $graph->setBackgroundColor(new Color(0xF4, 0xF4, 0xF4));
        $graph->shadow->setSize(3);

        $graph->title->set($this->title);
        $graph->title->setFont(new Tuffy(10));
        $graph->title->setColor(new Color(0x00, 0x00, 0x8B));
        $graph->border->setColor(new Color(187, 187, 187, 15));

        $plot = new Pie($this->xValues, $colors);
        $plot->setCenter(0.5, 0.45);
        $plot->setAbsSize(160, 160);
        
        $plot->setLegend($this->xLabels);
        $plot->legend->setModel(Legend::MODEL_BOTTOM);
        $plot->legend->setPosition(NULL, 1.25); /*$this->legendOffset*/
        $plot->legend->shadow->setSize(0);

        $graph->add($plot);

        $graph->draw();
    }
}

/**
 *	Comparison Chart Generator
 *	@package    CATS
 *	@subpackage Library
 */
class GraphComparisonChart
{
    private $xLabels;
    private $xValues;
    private $color;
    private $title;
    private $totalValue;


    public function __construct($xLabels, $xValues, $colorArray, $title, $width, $height, $totalValue)
    {
        $this->xLabels = $xLabels;
        $this->xValues = $xValues;
        $this->colorArray = $colorArray;
        $this->title = $title;
        $this->width = $width;
        $this->height = $height;
        $this->totalValue = $totalValue;
    }

    // FIXME: Document me.
    public function draw($format = false)
    {
        /* Make sure we have GD support. */
        if (!function_exists('imagecreatefromjpeg'))
        {
            die();
        }

        if ($format === false)
        {
            $format = IMG_PNG;
        }

        $graph = new Graph($this->width, $this->height);

        $graph->setFormat($format);
        $graph->border->setColor(new Color(0xFF, 0xFF, 0xFF));
        $graph->setBackgroundColor(new Color(0xF4, 0xF4, 0xF4));
        $graph->noBorder = true;
        //$graph->shadow->setSize(3);

        $graph->title->set($this->title);
        $graph->title->setFont(new Tuffy(12));
        $graph->title->setColor(new Color(0x00, 0x00, 0x8B));
        $graph->border->setColor(new Color(187, 187, 187, 15));

        $plot = new BarPlotPipeline($this->xValues, 1, 1, 0, $this->totalValue);
        $plot->setPadding(15, 15, 35, 29);
        $plot->setBarColor(new DarkGreen);
        $plot->barBorder->hide(true);

        $plot->arrayBarBackground = $this->colorArray;

        $plot->label->set($this->xValues);
        $plot->label->setFormat('%.0f');
        $plot->label->setBackgroundColor(new Color(240, 240, 240, 15));
        $plot->label->border->setColor(new Color(187, 187, 187, 15));
        $plot->label->setPadding(5, 3, 1, 1);

        $plot->yAxis->hide();
        $plot->yAxis->setLabelNumber(12);

        $plot->xAxis->setLabelText($this->xLabels);
        $plot->xAxis->label->setFont(new Tuffy(8));

        $graph->add($plot);

        $graph->draw();
        die();
    }
}


/**
 *	Pipeline Report Graph Generator
 *	@package    CATS
 *	@subpackage Library
 */
class pipelineStatisticsGraph
{
    private $xLabels;
    private $xValues;
    private $color;
    private $totalValue;
    private $legend1;
    private $legend2;
    private $legend3;
    private $view;
    private $noData;
    

    public function __construct($xLabels, $xValues, $colorArray, $width, $height, $legend1, $legend2, $legend3, $view, $noData)
    {
        $this->xLabels = $xLabels;
        $this->xValues = $xValues;
        $this->colorArray = $colorArray;
        $this->width = $width;
        $this->height = $height;
        $this->legend1 = $legend1;
        $this->legend2 = $legend2;
        $this->legend3 = $legend3;
        $this->view = $view;
        $this->noData = $noData;
    }


    // FIXME: Document me.
    public function draw($format = false)
    {
        /* Make sure we have GD support. */
        if (!function_exists('imagecreatefromjpeg'))
        {
            die();
        }

        if ($format === false)
        {
            $format = IMG_PNG;
        }

        $graph = new Graph($this->width, $this->height, NULL, 0, $this->width-95);

        $graph->setFormat($format);
        $graph->setBackgroundColor(new Color(0xF4, 0xF4, 0xF4));
        $graph->shadow->setSize(0);

        $graph->border->setColor(new Color(0xD0, 0xD0, 0xD0));

        $plot = new BarPlotDashboard($this->xValues, 1, 1, 0, $this->totalValue, true, $this->noData);
        $plot->setPadding(25, 105, 10, 22);
        $plot->setBarColor(new DarkGreen);
        $plot->barBorder->hide(true);

        $plot->arrayBarBackground = $this->colorArray;

        $plot->label->set($this->xValues);
        $plot->label->setFormat('%.0f');
        $plot->label->setBackgroundColor(new Color(240, 240, 240, 15));
        $plot->label->border->setColor(new Color(187, 187, 187, 15));
        $plot->label->setPadding(3, 1, 0, 0);

        $plot->xAxis->setLabelText($this->xLabels);
        $plot->xAxis->label->setFont(new Tuffy(8));
        $plot->xAxis->setDashboardImageMode(true);
        $plot->xAxis->setColor(new Color(0xD0, 0xD0, 0xD0));
        $plot->yAxis->setColor(new Color(0xD0, 0xD0, 0xD0));
        $plot->yAxis->setDashboardImageMode(true);
        $plot->view = $this->view;

        $plot->yAxis->label->setFont(new Tuffy(8));

        $plot->legend->add($plot, $this->legend1, Legend::BACKGROUND);
        $plot->legend->add($plot, $this->legend2, Legend::BACKGROUND);
        $plot->legend->add($plot, $this->legend3, Legend::BACKGROUND);
        $plot->legend->setTextFont(new Tuffy(8));
        $plot->legend->setPosition(1, 0.825);
        $plot->legend->setPadding(3, 3, 3, 3, 3);
        $plot->legend->setBackgroundColor(new Color(0xFF, 0xFF, 0xFF));
        $plot->legend->border->setColor(new Color(0xD0, 0xD0, 0xD0));
        $plot->legend->shadow->setSize(0);

        $graph->add($plot);

        $graph->draw();
        die();
    }
}


/**
 *	Job Order Report Graph Generator
 *	@package    CATS
 *	@subpackage Library
 */
class jobOrderReportGraph
{
    private $xLabels;
    private $xValues;
    private $color;
    private $title;
    private $totalValue;


    public function __construct($xLabels, $xValues, $colorArray, $title, $width, $height)
    {
        $this->xLabels = $xLabels;
        $this->xValues = $xValues;
        $this->colorArray = $colorArray;
        $this->title = $title;
        $this->width = $width;
        $this->height = $height;
    }


    // FIXME: Document me.
    public function draw($format = false)
    {
        /* Make sure we have GD support. */
        if (!function_exists('imagecreatefromjpeg'))
        {
            die();
        }

        if ($format === false)
        {
            $format = IMG_JPEG;
        }

        $graph = new Graph($this->width, $this->height);

        $graph->setFormat($format);
        $graph->setBackgroundColor(new Color(0xF4, 0xF4, 0xF4));
        $graph->shadow->setSize(6);

        $graph->title->set($this->title);
        $graph->title->setFont(new Tuffy(48));
        $graph->title->setColor(new Color(0x00, 0x00, 0x8B));
        $graph->border->setColor(new Color(187, 187, 187, 15));

        $plot = new BarPlotPipeline($this->xValues, 1, 1, 0, $this->totalValue, false);
        $plot->setPadding(40, 40, 15, 45);
        $plot->setBarColor(new DarkGreen);
        $plot->barBorder->hide(true);

        $plot->arrayBarBackground = $this->colorArray;

        $plot->xAxis->setLabelText($this->xLabels);
        $plot->xAxis->label->setFont(new Tuffy(24));

        $plot->yAxis->label->setFont(new Tuffy(18));

        $graph->add($plot);

        $graph->draw();
        die();
    }
}


/**
 *	Word Verification / CAPTCHA Generator
 *	@package    CATS
 *	@subpackage Library
 */
class WordVerify
{
    private $text;


    public function __construct($text)
    {
        $this->text = $text;
    }

    // FIXME: Document me.
    public function draw()
    {
        $object = new AntiSpam();
        $object->setText($this->text);
        $object->draw();
    }
}

?>
