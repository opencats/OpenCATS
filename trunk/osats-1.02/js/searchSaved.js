/*
 * OSATS
 * GNU License
*/

function syncRowHeightsSaved()
{
    var recentObj = document.getElementById('searchRecent');
    var savedObj = document.getElementById('searchSaved');

    if (recentObj.offsetHeight > savedObj.offsetHeight)
    {
        savedObj.style.height = recentObj.offsetHeight + 'px';
        recentObj.style.height = recentObj.offsetHeight + 'px';
    }
    else if (recentObj.offsetHeight < savedObj.offsetHeight)
    {
        recentObj.style.height = savedObj.offsetHeight + 'px';
        savedObj.style.height = savedObj.offsetHeight + 'px';
    }
}

function gotoSearch(text, url)
{
    document.getElementById('searchText').value = text;
    document.location.href = url;
}