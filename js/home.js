/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

function swapHomeGraph(view)
{
    var homeGraphImage = document.getElementById("homeGraph");
    
    homeGraphImage.src = CATSIndexName + "?m=graphs&a=miniPlacementStatistics&width=495&height=230&view=" + view;
}

/* We don't need to mouseover. */

function trackTableHighlight()
{
    return;
}