<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */

// Artichow configuration

if (is_file(__DIR__ . "/Artichow.cfg.php")) { // For PHP 4+5 version
    require_once __DIR__ . "/Artichow.cfg.php";
}


// Some useful files
require_once ARTICHOW . "/common.php";
require_once ARTICHOW . "/Component.class.php";
require_once ARTICHOW . "/Image.class.php";

require_once ARTICHOW . "/inc/Grid.class.php";
require_once ARTICHOW . "/inc/Tools.class.php";
require_once ARTICHOW . "/inc/Drawer.class.php";
require_once ARTICHOW . "/inc/Math.class.php";
require_once ARTICHOW . "/inc/Tick.class.php";
require_once ARTICHOW . "/inc/Axis.class.php";
require_once ARTICHOW . "/inc/Legend.class.php";
require_once ARTICHOW . "/inc/Mark.class.php";
require_once ARTICHOW . "/inc/Label.class.php";
require_once ARTICHOW . "/inc/Text.class.php";
require_once ARTICHOW . "/inc/Color.class.php";
require_once ARTICHOW . "/inc/Font.class.php";
require_once ARTICHOW . "/inc/Gradient.class.php";

// Catch all errors
ob_start();

/**
 * A graph
 *
 * @package Artichow
 */
class awGraph extends awImage
{
    /**
     * Graph timing ?
     *
     * @var bool
     */
    protected $timing;

    /**
     * Components
     *
     * @var array
     */
    private $components = [];

    /**
     * Some labels to add to the component
     *
     * @var array
     */
    protected $labels = [];

    /**
     * Graph title
     *
     * @var Label
     */
    public $title;

    /**
     * Construct a new graph
     *
     * @param int $width Graph width
     * @param int $height Graph height
     * @param string $name Graph name for the cache (must be unique). Let it null to not use the cache.
     * @param int $timeout Cache timeout (unix timestamp)
     */
    public function __construct(
        $width = null,
        $height = null, /**
  * Graph name
  */
        protected $name = null, /**
  * Cache timeout
  */
        protected $timeout = 0,
        $alternativeSize = 0
    ) {
        parent::__construct();

        $this->setSize($width, $height, $alternativeSize);

        // Clean sometimes all the cache
        if (mt_rand(0, 5000) === 0) {
            awGraph::cleanCache();
        }

        if ($this->name !== null) {
            $file = ARTICHOW . "/cache/" . $this->name . "-time";

            if (is_file($file)) {
                $type = awGraph::cleanGraphCache($file);

                if ($type === null) {
                    awGraph::deleteFromCache($this->name);
                } else {
                    header("Content-Type: image/" . $type);
                    readfile(ARTICHOW . "/cache/" . $this->name . "");
                    exit;
                }
            }
        }


        $this->title = new awLabel(
            null,
            new awTuffy(16),
            null,
            0
        );
        $this->title->setAlign(awLabel::CENTER, awLabel::BOTTOM);
    }

    /**
     * Delete a graph from the cache
     *
     * @param string $name Graph name
     * @return bool TRUE on success, FALSE on failure
     */
    public static function deleteFromCache($name)
    {
        if (is_file(ARTICHOW . "/cache/" . $name . "-time")) {
            unlink(ARTICHOW . "/cache/" . $name . "");
            unlink(ARTICHOW . "/cache/" . $name . "-time");
        }
    }

    /**
     * Delete all graphs from the cache
     */
    public static function deleteAllCache()
    {
        $dp = opendir(ARTICHOW . "/cache");

        while ($file = readdir($dp)) {
            if ($file !== '.' and $file != '..') {
                unlink(ARTICHOW . "/cache/" . $file);
            }
        }
    }

    /**
     * Clean cache
     */
    public static function cleanCache()
    {
        $glob = glob(ARTICHOW . "/cache/*-time");

        foreach ($glob as $file) {
            $type = awGraph::cleanGraphCache($file);

            if ($type === null) {
                $name = preg_replace('#.*/(.*)\-time#', '$1', $file);
                awGraph::deleteFromCache($name);
            }
        }
    }

    /**
     * Enable/Disable Graph timing
     *
     * @param bool $timing
     */
    public function setTiming($timing)
    {
        $this->timing = (bool) $timing;
    }

    /**
     * Add a component to the graph
     */
    public function add(awComponent $component)
    {
        $this->components[] = $component;
    }

    /**
     * Add a label to the component
     *
     * @param int $x Position on X axis of the center of the text
     * @param int $y Position on Y axis of the center of the text
     */
    public function addLabel(awLabel $label, $x, $y)
    {
        $this->labels[] = [$label, $x, $y];
    }

    /**
     * Add a label to the component with aboslute position
     *
     * @param awPoint $point Text position
     */
    public function addAbsLabel(awLabel $label, awPoint $point)
    {
        $this->labels[] = [$label, $point];
    }

    /**
     * Build the graph and draw component on it
     * Image is sent to the user browser
     *
     * @param string $file Save the image in the specified file. Let it null to print image to screen.
     */
    public function draw($file = null)
    {
        if ($this->timing) {
            $time = microtimeFloat();
        }

        $this->create();

        foreach ($this->components as $component) {
            $this->drawComponent($component);
        }

        $this->drawTitle();
        $this->drawShadow();
        $this->drawLabels();

        if ($this->timing) {
            $this->drawTiming(microtimeFloat() - $time);
        }

        $this->send($file);

        if ($file === null) {
            $data = ob_get_contents();

            if ($this->name !== null) {
                if (is_writable(ARTICHOW . "/cache") === false) {
                    trigger_error("Cache directory is not writable");
                }

                $file = ARTICHOW . "/cache/" . $this->name . "";
                file_put_contents($file, $data);

                $file .= "-time";
                file_put_contents($file, $this->timeout . "\n" . $this->getFormat());
            }
        }
    }

    private function drawLabels()
    {
        $drawer = $this->getDrawer();

        foreach ($this->labels as $array) {
            if (count($array) === 3) {
                // Text in relative position
                [$label, $x, $y] = $array;

                $point = new awPoint(
                    $x * $this->width,
                    $y * $this->height
                );
            } else {
                // Text in absolute position
                [$label, $point] = $array;
            }

            $label->draw($drawer, $point);
        }
    }

    private function drawTitle()
    {
        $drawer = $this->getDrawer();

        $point = new awPoint(
            $this->width / 2,
            10
        );

        $this->title->draw($drawer, $point);
    }

    private function drawTiming($time)
    {
        $drawer = $this->getDrawer();

        $label = new awLabel();
        $label->set("(" . sprintf("%.3f", $time) . " s)");
        $label->setAlign(awLabel::LEFT, awLabel::TOP);
        $label->border->show();
        $label->setPadding(1, 0, 0, 0);
        $label->setBackgroundColor(new awColor(230, 230, 230, 25));

        $label->draw($drawer, new awPoint(5, $drawer->height - 5));
    }

    private static function cleanGraphCache($file)
    {
        [$time, $type] = explode("\n", file_get_contents($file));

        $time = (int) $time;

        if ($time !== 0 and $time < time()) {
            return null;
        } else {
            return $type;
        }
    }
}

registerClass('Graph');

/*
 * To preserve PHP 4 compatibility
 */
function microtimeFloat()
{
    [$usec, $sec] = explode(" ", microtime());
    return (float) $usec + (float) $sec;
}
