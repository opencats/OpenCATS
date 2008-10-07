/*
 * OSATS
 * GNU License
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