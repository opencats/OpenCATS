/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

function checkUnckeckEEOSettings(setting)
{
    document.getElementById("genderTracking").checked = setting;
    document.getElementById("ethnicTracking").checked = setting;
    document.getElementById("veteranTracking").checked = setting;
    document.getElementById("disabilityTracking").checked = setting;
}
