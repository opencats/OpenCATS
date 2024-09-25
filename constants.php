<?php
/*
 * CATS
 * Constants File
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
 * $Id: constants.php 3785 2007-12-03 21:59:23Z brian $
 */

$coreModules = [
    'home' => '1',
    'activity' => '2',
    'joborders' => '3',
    'candidates' => '4',
    'companies' => '5',
    'contacts' => '6',
    'lists' => '7',
    'calendar' => '8',
    'reports' => '9',
    'settings' => '10',
];

/* CATS Version */

define('CATS_VERSION', '0.9.8.2');


/* Copyright information at bottom of pages. */
define('COPYRIGHT_HTML', '&copy; 2007-2023 OpenCATS.');

/* HTTP response codes. */
define('HTTP_OK', 200);
define('HTTP_FILE_NOT_FOUND', 404);
define('HTTP_SERVER_ERROR', 500);

/* Data item type flags. */
define('DATA_ITEM_CANDIDATE', 100);
define('DATA_ITEM_COMPANY', 200);
define('DATA_ITEM_CONTACT', 300);
define('DATA_ITEM_JOBORDER', 400);
define('DATA_ITEM_BULKRESUME', 500);
define('DATA_ITEM_USER', 600);
define('DATA_ITEM_LIST', 700);
define('DATA_ITEM_PIPELINE', 800);
define('DATA_ITEM_DUPLICATE', 900);

/* Settings types. */
define('SETTINGS_MAILER', 1);
define('SETTINGS_CALENDAR', 2);
define('SETTINGS_EEO', 3);
define('SETTINGS_CAREER_PORTAL', 4);

/* Access level flags. */
define('ACCESS_LEVEL_DELETED', -100);
define('ACCESS_LEVEL_DISABLED', 0);
define('ACCESS_LEVEL_READ', 100);
define('ACCESS_LEVEL_EDIT', 200);
define('ACCESS_LEVEL_DELETE', 300);
define('ACCESS_LEVEL_DEMO', 350);
define('ACCESS_LEVEL_SA', 400);
define('ACCESS_LEVEL_MULTI_SA', 450);
define('ACCESS_LEVEL_ROOT', 500);

/* Calendar constants. */
define('CALENDAR_DAY_SUNDAY', 1);
define('CALENDAR_DAY_MONDAY', 2);
define('CALENDAR_DAY_TUESDAY', 3);
define('CALENDAR_DAY_WEDNSDAY', 4);
define('CALENDAR_DAY_THURSDAY', 5);
define('CALENDAR_DAY_FRIDAY', 6);
define('CALENDAR_DAY_SATURDAY', 7);

define('CALENDAR_MONTH_JANUARY', 1);
define('CALENDAR_MONTH_FEBRUARY', 2);
define('CALENDAR_MONTH_MARCH', 3);
define('CALENDAR_MONTH_APRIL', 4);
define('CALENDAR_MONTH_MAY', 5);
define('CALENDAR_MONTH_JUNE', 6);
define('CALENDAR_MONTH_JULY', 7);
define('CALENDAR_MONTH_AUGUST', 8);
define('CALENDAR_MONTH_SEPTEMBER', 9);
define('CALENDAR_MONTH_OCTOBER', 10);
define('CALENDAR_MONTH_NOVEMBER', 11);
define('CALENDAR_MONTH_DECEMBER', 12);

/* Time period flags for statistics and reporting. */
define('TIME_PERIOD_TODAY', 100);
define('TIME_PERIOD_YESTERDAY', 200);
define('TIME_PERIOD_THISWEEK', 300);
define('TIME_PERIOD_LASTWEEK', 400);
define('TIME_PERIOD_THISMONTH', 500);
define('TIME_PERIOD_LASTMONTH', 600);
define('TIME_PERIOD_THISYEAR', 700);
define('TIME_PERIOD_LASTYEAR', 800);
define('TIME_PERIOD_TODATE', 900);

define('TIME_PERIOD_LASTTWOWEEKS', 1000);

/* Pipeline status flag. */
define('PIPELINE_STATUS_NOSTATUS', 0);
define('PIPELINE_STATUS_NOCONTACT', 100);
define('PIPELINE_STATUS_CANDIDATE_REPLIED', 250);
define('PIPELINE_STATUS_CONTACTED', 200);
define('PIPELINE_STATUS_QUALIFYING', 300);
define('PIPELINE_STATUS_SUBMITTED', 400);
define('PIPELINE_STATUS_INTERVIEWING', 500);
define('PIPELINE_STATUS_OFFERED', 600);
define('PIPELINE_STATUS_NOTINCONSIDERATION', 650);
define('PIPELINE_STATUS_CLIENTDECLINED', 700);
define('PIPELINE_STATUS_PLACED', 800);

/* Extra field types. */
define('EXTRA_FIELD_TEXT', 1);
define('EXTRA_FIELD_TEXTAREA', 2);
define('EXTRA_FIELD_CHECKBOX', 3);
define('EXTRA_FIELD_DATE', 4);
define('EXTRA_FIELD_DROPDOWN', 5);
define('EXTRA_FIELD_RADIO', 6);

/* Date format flags. */
define('DATE_FORMAT_MMDDYY', 0x1);
define('DATE_FORMAT_DDMMYY', 0x2);
define('DATE_FORMAT_YYYYMMDD', 0x4);
define('DATE_FORMAT_SECONDS', 0x8);

/* Automated backup types (ASP). */
define('BACKUP_TAR', 1);
define('BACKUP_ZIP', 2);
define('BACKUP_CATS', 3);

/* Saved lists getAll flags. */
define('ALL_LISTS', 0);
define('STATIC_LISTS', 1);
define('DYNAMIC_LISTS', 2);

/* Upcoming events getHTML flags. */
define('UPCOMING_FOR_CALENDAR', 0);
define('UPCOMING_FOR_DASHBOARD', 1);
define('UPCOMING_FOR_DASHBOARD_FUP', 2);

/* Dashboard graph view flags. */
define('DASHBOARD_GRAPH_WEEKLY', 0);
define('DASHBOARD_GRAPH_MONTHLY', 1);
define('DASHBOARD_GRAPH_YEARLY', 2);


define('MILES_PER_LATLNG', 70);

define('SECONDS_IN_A_DAY', 86400); /* 60 * 60 * 24 */

/* Error messages. */
define(
    'ERROR_NO_PERMISSION',
    'You do not have permission to access the requested resource.'
);

/* Constants for the login module. */
define('DEFAULT_ADMIN_PASSWORD', 'cats');
define('DEFAULT_MAIL_FROM_ADDRESS', 'noreply@yourdomain.com');

/* Module data array offsets. */
define('MODULE_SETTINGS_ENTRIES', 3);
define('MODULE_SETTINGS_ENTRIES_USER_LEVEL', 2);
define('MODULE_SETTINGS_USER_CATEGORIES', 4);

/* Site ID under which to store CATS system administrative data, etc. */
define('CATS_ADMIN_SITE', 180);

/* Location of the XML export templates directory */
define('XML_EXPORT_TEMPLATES_DIR', './modules/xml/xml_templates');

/* Default XML export template to use (from above directory) */
define('DEFAULT_XML_EXPORT_TEMPLATE', 'rss');

/* Time Zones */
// FIXME: Support fractional GMT offsets.
$timeZones = [
    [-12,  'GMT-12:00 International Date Line West'],
    [-11,  'GMT-11:00 Midway Island, Samoa'],
    [-10,  'GMT-10:00 Hawaii'],
    [-9,   'GMT-09:00 Alaska'],
    [-8,   'GMT-08:00 Pacific Time (US and Canada)'],
    [-8,   'GMT-08:00 Tijuana, Baja California'],
    [-7,   'GMT-07:00 Mountain Time (US and Canada)'],
    [-7,   'GMT-07:00 Arizona'],
    [-7,   'GMT-07:00 Chihuahua, La Paz, Mazatlan - New'],
    [-7,   'GMT-07:00 Chihuahua, La Paz, Mazatlan - Old'],
    [-6,   'GMT-06:00 Central Time (US and Canada)'],
    [-6,   'GMT-06:00 Central America'],
    [-6,   'GMT-06:00 Guadalajara, Mexico City, Monterrey - New'],
    [-6,   'GMT-06:00 Guadalajara, Mexico City, Monterrey - Old'],
    [-6,   'GMT-06:00 Saskatchewan'],
    [-5,   'GMT-05:00 Eastern Time (US and Canada)'],
    [-5,   'GMT-05:00 Bogota, Lima, Quito, Rio Branco'],
    [-5,   'GMT-05:00 Indiana (East)'],
    [-4,   'GMT-04:00 Atlantic Time (Canada)'],
    [-4,   'GMT-04:00 Caracas, La Paz'],
    [-4,   'GMT-04:00 Manaus'],
    [-4,   'GMT-04:00 Santiago'],
    //array(-3.5, 'GMT-03:30 Newfoundland'),
    [-3,   'GMT-03:00 Greenland'],
    [-3,   'GMT-03:00 Brasilia'],
    [-3,   'GMT-03:00 Buenos Aires, Georgetown'],
    [-3,   'GMT-03:00 Montevideo'],
    [-2,   'GMT-02:00 Mid-Atlantic'],
    [-1,   'GMT-01:00 Azores'],
    [-1,   'GMT-01:00 Cape Verde Is.'],
    [0,    'GMT Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London'],
    [0,    'GMT Casablanca, Monrovia, Reykjavik'],
    [1,    'GMT+01:00 Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna'],
    [1,    'GMT+01:00 Belgrade, Bratislava, Budapest, Ljubljana, Prague'],
    [1,    'GMT+01:00 Brussels, Copenhagen, Madrid, Paris'],
    [1,    'GMT+01:00 Sarajevo, Skopje, Warsaw, Zagreb'],
    [1,    'GMT+01:00 West Central Africa'],
    [2,    'GMT+02:00 Amman'],
    [2,    'GMT+02:00 Athens, Bucharest, Istanbul'],
    [2,    'GMT+02:00 Beirut'],
    [2,    'GMT+02:00 Cairo'],
    [2,    'GMT+02:00 Harare, Pretoria'],
    [2,    'GMT+02:00 Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius'],
    [2,    'GMT+02:00 Jerusalem'],
    [2,    'GMT+02:00 Minsk'],
    [2,    'GMT+02:00 Windhoek'],
    [3,    'GMT+03:00 Baghdad'],
    [3,    'GMT+03:00 Kuwait, Riyadh'],
    [3,    'GMT+03:00 Moscow, St. Petersburg, Volgograd'],
    [3,    'GMT+03:00 Nairobi'],
    [3,    'GMT+03:00 Tbilisi'],
    //array(3.5,  'GMT+03:30 Tehran'),
    [4,    'GMT+04:00 Abu Dhabi, Muscat'],
    [4,    'GMT+04:00 Baku'],
    [4,    'GMT+04:00 Yerevan'],
    //array(4.5,  'GMT+04:30 Kabul'),
    [5,    'GMT+05:00 Ekaterinburg'],
    [5,    'GMT+05:00 Islamabad, Karachi, Tashkent'],
    //array(5.5,  'GMT+05:30 Chennai, Kolkata, Mumbai, New Delhi'),
    //array(5.5,  'GMT+05:30 Sri Jayawardenepura'),
    //array(5.75, 'GMT+05:45 Kathmandu'),
    [6,    'GMT+06:00 Almaty, Novosibirsk'],
    [6,    'GMT+06:00 Astana, Dhaka'],
    //array(6.5,  'GMT+06:30 Yangon (Rangoon)'),
    [7,    'GMT+07:00 Bangkok, Hanoi, Jakarta'],
    [7,    'GMT+07:00 Krasnoyarsk'],
    [8,    'GMT+08:00 Beijing, Chongqing, Hong Kong, Urumqi'],
    [8,    'GMT+08:00 Irkutsk, Ulaan Bataar'],
    [8,    'GMT+08:00 Kuala Lumpur, Singapore'],
    [8,    'GMT+08:00 Perth'],
    [8,    'GMT+08:00 Taipei'],
    [9,    'GMT+09:00 Osaka, Sapporo, Tokyo'],
    [9,    'GMT+09:00 Seoul'],
    [9,    'GMT+09:00 Yakutsk'],
    //array(9.5,  'GMT+09:30 Adelaide'),
    //array(9.5,  'GMT+09:30 Darwin'),
    [10,   'GMT+10:00 Brisbane'],
    [10,   'GMT+10:00 Canberra, Melbourne, Sydney'],
    [10,   'GMT+10:00 Guam, Port Moresby'],
    [10,   'GMT+10:00 Hobart'],
    [10,   'GMT+10:00 Vladivostok'],
    [11,   'GMT+11:00 Magadan, Solomon Is., New Caledonia'],
    [12,   'GMT+12:00 Auckland, Wellington'],
    [12,   'GMT+12:00 Fiji, Kamchatka, Marshall Is.'],
    [12,   'GMT+13:00 Nuku`alofa'],
];

/* These file extensions will have '.txt' appended to them on upload. */
$badFileExtensions = [
    'shtml',
    'php',
    'php5',
    'php4',
    'phps',
    'cgi',
    'pl',
    'py',
];
