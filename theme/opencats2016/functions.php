<?php
function opencats2016_nav(){
    $active = null;
    return TemplateUtility::printTabs($active);
}

function opencats2016_scripts(){
    oc_enqueue_script('opencats2016-lib', '/js/lib.js');
    oc_enqueue_script('opencats2016-quickaction', '/js/quickAction.js');
    oc_enqueue_script('opencats2016-calendardateinput', '/js/calendarDateInput.js');
    oc_enqueue_script('opencats2016-submodal', '/js/submodal/subModal.js');
    oc_enqueue_script('opencats2016-jquery', '/js/jquery-1.3.2.min.js');

    oc_enqueue_script('opencats2016-sweettitles', '/js/sweetTitles.js');
    oc_enqueue_script('opencats2016-datagrid', '/js/dataGrid.js');
    oc_enqueue_script('opencats2016-datagridfilters', '/js/dataGridFilters.js');
    oc_enqueue_script('opencats2016-home', '/js/home.js');


    oc_enqueue_style('opencats2016-main', '/main.css');

    oc_enqueue_style('opencats2016-bootstrap', 'vendor/twbs/bootstrap/dist/css/bootstrap.min.css');
    oc_enqueue_script('opencats2016-jquery', 'vendor/components/jquery/jquery.min.js');
    oc_enqueue_script('opencats2016-bootstrap', 'vendor/twbs/bootstrap/dist/js/bootstrap.min.js');
}
add_filter('oc_enqueue_scripts', 'opencats2016_scripts');