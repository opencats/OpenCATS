/*
 * CATS
 * Home Tab JavaScript Library
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
 * $Id: home.js 3548 2007-11-09 23:54:52Z andrew $
 */

function swapHomeGraph(view)
{
    var homeGraphImage = document.getElementById('homeGraph');
    
    homeGraphImage.src = CATSIndexName + "?m=graphs&a=miniPlacementStatistics&width=495&height=230&view=" + view;
}

/* We don't need to mouseover. */

function trackTableHighlight()
{
    return;
}