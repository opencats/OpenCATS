<?php
namespace OpenCATS\Tests\IntegrationTests;

use DatabaseConnection;

class ChartDataTest extends DatabaseTestCase
{
    public function testExistingQueriesAndChartDataAcrossRangesAndFilters()
    {
        $_SESSION['CATS'] = new class {
            public function getUserID() { return 1; }
            public function isLoggedIn() { return false; }
            public function isDateDMY() { return true; }
            public function isTimeFormat24() { return true; }
            public function getTimeZoneOffset() { return 0; }
        };
        include_once(LEGACY_ROOT . '/lib/Dashboard.php');
        include_once(LEGACY_ROOT . '/lib/Statistics.php');
        include_once(LEGACY_ROOT . '/lib/Charts.php');
        $db = DatabaseConnection::getInstance();
        $db->query("SET timestamp = UNIX_TIMESTAMP('2026-09-25 12:00:00')");
        try
        {
            $db->query("DELETE FROM settings WHERE settings_type = " . SETTINGS_CALENDAR . " AND setting = 'firstDayMonday'");
            $db->query("INSERT INTO settings (settings_type, setting, value) VALUES (" . SETTINGS_CALENDAR . ", 'firstDayMonday', '0')");
            $dashboard = new \Dashboard();
            foreach (array(0, 1, 2) as $view)
            {
                $chart = \Charts::hiring($dashboard->getPipelineData($view));
                $this->assertCount(4, $chart['labels']);
                $this->assertSame(array(0, 0, 0, 0), $chart['datasets'][0]['data']);
            }
            // Repeated transitions deliberately count as separate events.
            foreach (array(array('2026-09-25', 400), array('2026-09-25', 400),
                array('2026-09-18', 500), array('2026-08-15', 800), array('2025-04-10', 800)) as $event)
            {
                $db->query("INSERT INTO candidate_joborder_status_history (candidate_id, joborder_id, date, status_from, status_to) VALUES (1, 1, '" . $event[0] . "', 0, " . $event[1] . ")");
            }
            $weekly = \Charts::hiring($dashboard->getPipelineData(0));
            $this->assertSame(array('30/08 - 05/09', '06/09 - 12/09', '13/09 - 19/09', '20/09 - 26/09'), $weekly['labels']);
            $this->assertSame(array(0, 0, 0, 2), $weekly['datasets'][0]['data']);
            $this->assertSame(array(0, 0, 1, 0), $weekly['datasets'][1]['data']);
            $monthly = \Charts::hiring($dashboard->getPipelineData(1));
            $this->assertSame(array('June', 'July', 'August', 'September'), $monthly['labels']);
            $this->assertSame(array(0, 0, 1, 0), $monthly['datasets'][2]['data']);
            $yearly = \Charts::hiring($dashboard->getPipelineData(2));
            $this->assertEquals(array(2023, 2024, 2025, 2026), $yearly['labels']);
            $this->assertSame(array(0, 0, 1, 1), $yearly['datasets'][2]['data']);
            $db->query("UPDATE settings SET value = '1' WHERE settings_type = " . SETTINGS_CALENDAR . " AND setting = 'firstDayMonday'");
            $monday = \Charts::hiring($dashboard->getPipelineData(0));
            $this->assertSame('21/09 - 27/09', $monday['labels'][3]);

            $db->query("INSERT INTO joborder (joborder_id, title, status) VALUES (1, 'Chart fixture', 'Active'), (2, 'Other job', 'Active')");
            $db->query("INSERT INTO candidate (candidate_id, first_name, eeo_gender, eeo_disability_status, eeo_ethnic_type_id, eeo_veteran_type_id, date_modified) VALUES (1, 'Chart', 'm', 'Yes', 1, 1, NOW()), (2, 'Older', 'f', 'No', 1, 1, '2020-01-01')");
            $db->query("INSERT INTO candidate_joborder (candidate_id, joborder_id, status) VALUES (1, 1, 800), (2, 1, 400), (1, 2, 500)");
            $statistics = new \Statistics();
            $pipeline = \Charts::pipeline($statistics->getPipelineData(1));
            $this->assertSame(array(2, 2, 2, 2, 2, 1, 1, 0, 0, 1), $pipeline['datasets'][0]['data']);
            $this->assertSame(array_fill(0, 10, 0), \Charts::pipeline($statistics->getPipelineData(999))['datasets'][0]['data']);
            $db->query("UPDATE joborder SET status = 'Closed' WHERE joborder_id = 1");
            $this->assertSame(array_fill(0, 10, 0), \Charts::pipeline($statistics->getPipelineData(1))['datasets'][0]['data']);
            foreach (array('week', 'month') as $period)
            {
                $eeo = $statistics->getEEOReport($period, 'all');
                $this->assertEquals(1, $eeo['rsGenderStatistics']['numberOfCandidatesMale']);
                $this->assertEquals(0, $eeo['rsGenderStatistics']['numberOfCandidatesFemale']);
                $this->assertEquals(1, $eeo['rsDisabledStatistics']['numberOfCandidatesDisabled']);
                $this->assertEquals(1, $eeo['rsEthnicStatistics'][0]['numberOfCandidates']);
                $this->assertEquals(1, $eeo['rsVeteranStatistics'][0]['numberOfCandidates']);
            }
            $this->assertEquals(1, $statistics->getEEOReport('all', 'all')['rsGenderStatistics']['numberOfCandidatesFemale']);
            $this->assertEquals(1, $statistics->getEEOReport('all', 'placed')['rsGenderStatistics']['numberOfCandidatesMale']);
            $this->assertEquals(0, $statistics->getEEOReport('all', 'rejected')['rsGenderStatistics']['numberOfCandidatesMale']);
            // Preserve the existing join multiplicity; tracked separately as an EEO defect.
            $db->query("INSERT INTO joborder (joborder_id, title, status) VALUES (3, 'Second placement', 'Active')");
            $db->query("INSERT INTO candidate_joborder (candidate_id, joborder_id, status) VALUES (1, 3, 800)");
            $this->assertEquals(2, $statistics->getEEOReport('all', 'placed')['rsGenderStatistics']['numberOfCandidatesMale']);
            $report = $statistics->getJobOrderReport(1);
            $this->assertEquals(2, $report['pipeline']);
            $this->assertEquals(2, $report['submitted']);
            $this->assertEquals(1, $report['pipelineInterving']);
            $this->assertEquals(2, $report['pipelinePlaced']);
        }
        finally
        {
            $db->query('SET timestamp = DEFAULT');
            unset($_SESSION['CATS']);
        }
    }
}
