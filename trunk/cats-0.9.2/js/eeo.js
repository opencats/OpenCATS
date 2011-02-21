/*
 * CATS
 * Equal Employment Opportunity JavaScript Library
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
 * $Id: eeo.js 2336 2007-04-14 22:01:51Z will $
 */

function checkUnckeckEEOSettings(setting)
{
    document.getElementById('genderTracking').checked = setting;
    document.getElementById('ethnicTracking').checked = setting;
    document.getElementById('veteranTracking').checked = setting;
    document.getElementById('disabilityTracking').checked = setting;
}
