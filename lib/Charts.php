<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

include_once(LEGACY_ROOT . '/lib/Template.php');

/** Shared chart data boundary and accessible markup. Queries stay in their existing libraries. */
class Charts
{
    public static function comparison($title, $labels, $values, $description = '')
    {
        return array(
            'title' => $title,
            'description' => $description !== '' ? $description : $title,
            'horizontal' => true,
            'labels' => array_values($labels),
            'datasets' => array(array('label' => 'Candidates', 'data' => array_map('intval', array_values($values))))
        );
    }

    public static function distribution($title, $labels, $values, $description)
    {
        $chart = self::comparison($title, $labels, $values, $description);
        $total = array_sum($chart['datasets'][0]['data']);
        $chart['percentageLabel'] = 'of recorded categories';
        $chart['percentages'] = array();
        foreach ($chart['datasets'][0]['data'] as $value)
        {
            // Retain the whole-percent labels shown by the former pie renderer.
            $chart['percentages'][] = $total > 0 ? round($value / $total * 100) : 0;
        }
        return $chart;
    }

    public static function hiring($rows)
    {
        $rows = array_values($rows);
        $chart = array(
            'title' => 'Hiring Overview',
            'description' => 'Status-change events across four periods. The latest period is incomplete; repeated transitions count separately.',
            'horizontal' => false,
            'labels' => array_column($rows, 'label'),
            'datasets' => array()
        );
        foreach (array('submitted' => 'Submissions', 'interviewing' => 'Interviews', 'placed' => 'Hires') as $key => $label)
        {
            $chart['datasets'][] = array('label' => $label, 'data' => array_map('intval', array_column($rows, $key)));
        }
        return $chart;
    }

    public static function pipeline($statisticsData)
    {
        // Preserve the cumulative progression used by the former pipeline graph.
        $x = array_fill(0, 10, 0);
        $x[9] = (int) ($statisticsData['placed'] ?? 0);
        $x[8] = (int) ($statisticsData['passedOn'] ?? 0);
        $x[7] = (int) ($statisticsData['candidateDeclined'] ?? 0);
        $x[6] = (int) ($statisticsData['offered'] ?? 0) + $x[9];
        $x[5] = (int) ($statisticsData['interviewing'] ?? 0) + $x[6];
        $x[4] = (int) ($statisticsData['submitted'] ?? 0) + $x[5];
        $x[3] = (int) ($statisticsData['qualifying'] ?? 0) + $x[4];
        $x[2] = (int) ($statisticsData['replied'] ?? 0) + $x[3];
        $x[1] = (int) ($statisticsData['contacted'] ?? 0) + $x[2];
        $x[0] = (int) ($statisticsData['totalPipeline'] ?? 0);
        $chart = self::comparison('Status of Candidates', array(
            'Total Pipeline', 'Contacted', 'Candidate Replied', 'Qualifying', 'Submitted',
            'Interviewing', 'Offered', 'Candidate Declined', 'Client Declined', 'Placed'
        ), $x, 'Progression counts include later active stages. Declines are separate; categories overlap. Closed job orders are excluded.');
        $chart['percentageLabel'] = 'of pipeline';
        $maximum = $x[0];
        $chart['percentages'] = array();
        foreach ($x as $value)
        {
            $maximum = max(1, $maximum, $value);
            $chart['percentages'][] = round($value / $maximum * 100);
        }
        return $chart;
    }

    public static function render($id, $chart, $existingTable = false)
    {
        $escape = array('Template', 'escapeHtml');
        $title = $escape($chart['title']);
        $id = Template::escapeAttr($id);
        $hasData = false;
        foreach ($chart['datasets'] as $dataset)
        {
            foreach ($dataset['data'] as $value)
            {
                $hasData = $hasData || $value != 0;
            }
        }
        // Template::escapeJs normalizes values to strings, so encode the structure here.
        $json = json_encode($chart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
        $html = '<div id="' . $id . '" class="oc-chart" data-chart-state="pending">';
        $html .= '<p class="small text-body-secondary mb-2">' . $escape($chart['description']) . '</p>';
        if ($hasData)
        {
            $html .= '<div class="oc-chart-plot" hidden><canvas role="img" aria-label="' . $title . '. Values are available in the table."></canvas></div>';
            $html .= '<p class="oc-chart-status small text-body-secondary">Chart unavailable. The values remain available in the table.</p>';
        }
        else
        {
            $html .= '<p class="oc-chart-status alert alert-secondary py-2" role="status">No data to chart for this selection.</p>';
        }
        if (!$existingTable)
        {
            $html .= '<details class="oc-chart-values small" open><summary>View values: ' . $title . '</summary>';
            $html .= '<div class="table-responsive"><table class="table table-sm mb-0"><caption class="visually-hidden">' . $title . '</caption><thead><tr><th scope="col">' . ($chart['horizontal'] ? 'Category' : 'Period') . '</th>';
            foreach ($chart['datasets'] as $dataset)
            {
                $html .= '<th scope="col" class="text-end">' . $escape($dataset['label']) . '</th>';
            }
            if (isset($chart['percentages']))
            {
                $html .= '<th scope="col" class="text-end">% ' . $escape($chart['percentageLabel']) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($chart['labels'] as $index => $label)
            {
                $html .= '<tr><th scope="row" class="fw-normal">' . $escape($label) . '</th>';
                foreach ($chart['datasets'] as $dataset)
                {
                    $html .= '<td class="text-end">' . $escape($dataset['data'][$index]) . '</td>';
                }
                if (isset($chart['percentages']))
                {
                    $html .= '<td class="text-end">' . $escape($chart['percentages'][$index]) . '%</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div></details>';
        }
        return $html . '<script type="application/json" class="oc-chart-data">' . $json . '</script></div>';
    }
}
