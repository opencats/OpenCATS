<?php
/* Disposable fixtures for manual chart review in the local test database only.
 * Run: php test/scripts/chartVisualFixtures.php create|zero|remove
 */
include_once './config.php';
include_once './constants.php';
include_once './lib/DatabaseConnection.php';
if (DATABASE_NAME !== 'cats_test') die("Requires cats_test.\n");
$db = DatabaseConnection::getInstance();
$manifest = sys_get_temp_dir() . '/opencats-chart-fixtures.json';
if (($argv[1] ?? '') === 'zero')
{
    $ids = json_decode(file_get_contents($manifest), true);
    $candidates = implode(',', array_map('intval', $ids['candidates']));
    $db->query('DELETE FROM candidate_joborder_status_history WHERE candidate_id IN (' . $candidates . ')');
    $db->query("UPDATE candidate SET eeo_gender = '', eeo_disability_status = '', eeo_ethnic_type_id = 0, eeo_veteran_type_id = 0 WHERE candidate_id IN (" . $candidates . ')');
    echo "Chart fixtures now have no hiring events or recorded EEO categories.\n";
    exit;
}
if (($argv[1] ?? '') === 'remove')
{
    $ids = json_decode(file_get_contents($manifest), true);
    $candidates = implode(',', array_map('intval', $ids['candidates']));
    $db->query('DELETE FROM candidate_joborder_status_history WHERE candidate_id IN (' . $candidates . ')');
    $db->query('DELETE FROM candidate_joborder WHERE candidate_id IN (' . $candidates . ')');
    $db->query('DELETE FROM candidate WHERE candidate_id IN (' . $candidates . ')');
    $db->query('DELETE FROM joborder WHERE joborder_id IN (' . implode(',', array_map('intval', $ids['jobs'])) . ')');
    $db->query('DELETE FROM settings WHERE settings_type = ' . SETTINGS_EEO);
    foreach ($ids['settings'] as $row)
    {
        $db->query('INSERT INTO settings (settings_type, setting, value) VALUES (' . SETTINGS_EEO . ', '
            . $db->makeQueryString($row['setting']) . ', ' . $db->makeQueryString($row['value']) . ')');
    }
    unlink($manifest);
    echo "Removed chart fixtures and restored EEO settings.\n";
    exit;
}
if (($argv[1] ?? '') !== 'create' || file_exists($manifest)) die("Use create once, then remove.\n");
$ids = array('candidates' => array(), 'jobs' => array(), 'settings' => $db->getAllAssoc('SELECT setting, value FROM settings WHERE settings_type = ' . SETTINGS_EEO));
foreach (array('Populated', 'Empty') as $kind)
{
    $db->query("INSERT INTO joborder (title, status, entered_by, owner, recruiter, company_id, date_created) VALUES ('Chart review " . $kind . "', 'Active', 1, 1, 1, 10001, NOW())");
    $ids['jobs'][] = (int) $db->getLastInsertID();
}
for ($i = 0; $i < 24; ++$i)
{
    $db->query("INSERT INTO candidate (first_name, last_name, owner, entered_by, eeo_gender, eeo_disability_status, eeo_ethnic_type_id, eeo_veteran_type_id, date_created, date_modified) VALUES ('ChartReview', 'Fixture', 1, 1, '" . ($i % 3 ? 'm' : 'f') . "', '" . ($i % 4 ? 'No' : 'Yes') . "', " . ($i % 7 + 1) . ', ' . ($i % 5 + 1) . ', NOW(), NOW())');
    $id = (int) $db->getLastInsertID();
    $ids['candidates'][] = $id;
    $statuses = array(100, 200, 250, 300, 400, 400, 500, 500, 600, 650, 700, 800);
    $db->query('INSERT INTO candidate_joborder (candidate_id, joborder_id, status) VALUES (' . $id . ', ' . $ids['jobs'][0] . ', ' . $statuses[$i % 12] . ')');
    foreach (array('WEEK', 'MONTH', 'YEAR') as $unit)
    {
        $db->query('INSERT INTO candidate_joborder_status_history (candidate_id, joborder_id, date, status_from, status_to) VALUES (' . $id . ', ' . $ids['jobs'][0] . ', DATE_SUB(NOW(), INTERVAL ' . ($i % 4) . ' ' . $unit . '), 0, ' . array(400, 500, 800)[$i % 3] . ')');
    }
}
$db->query('DELETE FROM settings WHERE settings_type = ' . SETTINGS_EEO);
foreach (array('ethnicTracking', 'veteranTracking', 'genderTracking', 'disabilityTracking') as $setting)
{
    $db->query("INSERT INTO settings (settings_type, setting, value) VALUES (" . SETTINGS_EEO . ", '" . $setting . "', '1')");
}
file_put_contents($manifest, json_encode($ids));
echo 'Review job IDs: ' . implode(', ', $ids['jobs']) . "\n";
