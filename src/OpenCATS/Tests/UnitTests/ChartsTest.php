<?php
use PHPUnit\Framework\TestCase;

include_once(LEGACY_ROOT . '/lib/Charts.php');

class ChartsTest extends TestCase
{
    public function testHiringPreservesOrderedPeriodsAndEventCounts()
    {
        $chart = Charts::hiring(array(
            100 => array('label' => 'Earlier', 'submitted' => '12', 'interviewing' => '5', 'placed' => '0'),
            200 => array('label' => 'Current', 'submitted' => '3', 'interviewing' => '2', 'placed' => '1')
        ));
        $this->assertSame(array('Earlier', 'Current'), $chart['labels']);
        $this->assertSame(array('Submissions', 'Interviews', 'Hires'), array_column($chart['datasets'], 'label'));
        $this->assertSame(array(array(12, 3), array(5, 2), array(0, 1)), array_column($chart['datasets'], 'data'));
    }

    public function testPipelinePreservesCumulativeCountsAndPercentages()
    {
        $chart = Charts::pipeline(array('totalPipeline' => 40, 'contacted' => 6, 'replied' => 5,
            'qualifying' => 4, 'submitted' => 3, 'interviewing' => 2, 'offered' => 1,
            'placed' => 1, 'candidateDeclined' => 7, 'passedOn' => 8));
        $this->assertSame(array(40, 22, 16, 11, 7, 4, 2, 7, 8, 1), $chart['datasets'][0]['data']);
        $this->assertEquals(array(100, 55, 40, 28, 18, 10, 5, 18, 20, 3), $chart['percentages']);
        $this->assertSame('Candidate Declined', $chart['labels'][7]);
        $this->assertSame('Client Declined', $chart['labels'][8]);
    }

    public function testDistributionPreservesPiePercentagesAndZeroValues()
    {
        $chart = Charts::distribution('Gender', array('Male', 'Female'), array(1, 2), 'Recorded only');
        $this->assertSame(array(1, 2), $chart['datasets'][0]['data']);
        $this->assertEquals(array(33, 67), $chart['percentages']);
        $this->assertSame('of recorded categories', $chart['percentageLabel']);
        $zero = Charts::distribution('Gender', array('Male', 'Female'), array(0, 0), 'Recorded only');
        $this->assertSame(array(0, 0), $zero['percentages']);
        $this->assertStringNotContainsString('<canvas', Charts::render('zero-distribution', $zero));
    }

    public function testEmptyAndZeroHaveReadableValuesWithoutCanvas()
    {
        foreach (array(Charts::hiring(array()), Charts::pipeline(array()),
            Charts::comparison('Gender', array('Male', 'Female'), array(0, null))) as $chart)
        {
            $html = Charts::render('empty', $chart);
            $this->assertStringContainsString('No data to chart', $html);
            $this->assertStringNotContainsString('<canvas', $html);
            $this->assertStringContainsString('<table', $html);
        }
    }

    public function testSafeBoundaryKeepsFullLabelsAndFallbackWithoutDuplicatingExistingTables()
    {
        $label = 'Long category, including </script><img src=x onerror=alert(1)> & café';
        $chart = Charts::comparison('EEO', array($label), array('9'));
        $html = Charts::render('eeo', $chart);
        $this->assertStringNotContainsString('<img', $html);
        preg_match('~<script type="application/json" class="oc-chart-data">(.*?)</script>~s', $html, $match);
        $this->assertSame($chart, json_decode($match[1], true));
        $this->assertStringContainsString('Chart unavailable', $html);
        $this->assertStringContainsString('<details', $html);
        $this->assertStringNotContainsString('<table', Charts::render('eeo', $chart, true));
    }

    public function testApplicationHasNoLegacyGraphDependency()
    {
        $root = dirname(__DIR__, 4);
        foreach (array('lib', 'modules', 'js') as $directory)
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory));
            foreach ($files as $file)
            {
                if (!$file->isFile() || !in_array($file->getExtension(), array('php', 'tpl', 'js', 'css'))) continue;
                $this->assertDoesNotMatchRegularExpression('~artichow|GraphGenerator|lib/Graphs\.php|new (?:GraphSimple|GraphPie|GraphComparisonChart|pipelineStatisticsGraph|WordVerify)\b~i',
                    file_get_contents($file->getPathname()), $file->getPathname());
            }
        }
        $this->assertDirectoryDoesNotExist($root . '/lib/artichow');
        $this->assertFileDoesNotExist($root . '/modules/graphs/GraphsUI.php');
    }
}
