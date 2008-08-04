var wizardPages = Array();
var optionDisableNext = Array();
var optionDisableSkip = Array();
var lastRequestAction = '';
var nextDisabled = false;
var previousDisabled = false;

var dotDanceStage = 0;
var loadingBarShown = false;

function enableNext()
{
    var obj = document.getElementById('next');
    if (currentPage <= wizardPages.length)
    {
        if (obj)
        {
            nextDisabled = false;
            obj.style.color = '#000000';
        }
    }
}

function disableNext()
{
    var obj = document.getElementById('next');
    if (obj)
    {
        nextDisabled = true;
        obj.style.color = '#c0c0c0';
    }
}

function enablePrevious()
{
    var obj = document.getElementById('previous');
    if (currentPage < wizardPages.length)
    {
        if (obj)
        {
            previousDisabled = false;
            obj.style.color = '#000000';
        }
    }
}

function disablePrevious()
{
    var obj = document.getElementById('previous');
    if (obj)
    {
        previousDisabled = true;
        obj.style.color = '#c0c0c0';
    }
}

function enableSkip()
{
    var obj = document.getElementById('skip');
    obj.style.display = 'inline';
}

function disableSkip()
{
    var obj = document.getElementById('skip');
    obj.style.display = 'none';
}

function addWizardPage(title, nonext, noskip)
{
    wizardPages.push(title);
    optionDisableNext.push(nonext);
    optionDisableSkip.push(noskip);
}

function next()
{
    if (nextDisabled) return;

    if (typeof extendedNext == 'function')
    {
        if (!extendedNext()) return;
    }

    if (currentPage == wizardPages.length)
    {
        document.location.href = finishURL;
    }
    else if (currentPage < wizardPages.length)
    {
        loadPage('next');
    }
}

function funcNext()
{
    if (currentPage == wizardPages.length)
    {
        document.location.href = finishURL;
    }
    else if (currentPage < wizardPages.length)
    {
        loadPage('next');
    }
}

function previous()
{
    if (previousDisabled) return;

    if (currentPage > 1)
    {
        loadPage('previous');
    }
}

function skip()
{
    document.location.href = finishURL;
}

function current()
{
    loadPage('current');
}

function loadPage(requestAction)
{
    var ajaxObj;
    var url = '?m=wizard&a=ajax_getPage&currentPage=' + currentPage + '&requestAction=' + requestAction;
    lastRequestAction = requestAction;

    // If the wizard page takes longer than 1.5 seconds, show a loading bar.
    loadingBarShown = true;
    setTimeout('showLoadingBar()', 1500);

    try
    {
        // Firefox, Opera 8.0+, Safari
        ajaxObj = new XMLHttpRequest();
    }
    catch (e)
    {
        // Internet Explorer
        try
        {
            ajaxObj = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e)
        {
            try
            {
                ajaxObj = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e)
            {
                alert("Your browser does not support AJAX!");
                return false;
            }
        }
    }
    ajaxObj.onreadystatechange = function()
    {
        if (ajaxObj.readyState == 4)
        {
            var wizardBody = document.getElementById('wizardContainerBody');

            hideLoadingBar();

            if (lastRequestAction != '')
            {
                switch (lastRequestAction)
                {
                    case 'next':
                        currentPage++;
                        break;

                    case 'previous':
                        currentPage--;
                        break;
                }
            }

            if (wizardBody)
            {
                var title = document.getElementById('pageTitle');
                title.innerHTML = wizardPages[currentPage-1];
                if (optionDisableNext[currentPage-1])
                {
                    disableNext();
                }
                else
                {
                    enableNext();
                }
                if (optionDisableSkip[currentPage-1])
                {
                    disableSkip();
                }
                else
                {
                    enableSkip();
                }

                var obj = document.getElementById('next');
                if (obj)
                {
                    if (currentPage == wizardPages.length)
                    {
                        obj.value = 'Finish';
                    }
                    else
                    {
                        obj.value = 'Next';
                    }
                }

                if (currentPage == 1)
                {
                    disablePrevious();
                }
                else
                {
                    enablePrevious();
                }

                // Change the top tabs
                for (var i=0; i<wizardPages.length; i++)
                {
                    var sectionObj = document.getElementById('section' + (i+1));
                    if (currentPage == (i+1)) sectionObj.className = 'sectionTitleCurrent';
                    else sectionObj.className = 'sectionTitle';
                }

                wizardBody.innerHTML = ajaxObj.responseText;
            }
        }
    }

    ajaxObj.open("GET",url,true);
    ajaxObj.send(null);
    parsingBlock = true;
}

function loadingBarDotDance()
{
    var dot1 = document.getElementById('loading1Dot');
    var dot2 = document.getElementById('loading2Dot');
    var dot3 = document.getElementById('loading3Dot');

    if (dot1 && dot2 && dot3)
    {
        switch (dotDanceStage)
        {
            case 0:
                dot1.style.color = '#666666';
                dot2.style.color = '#d0d0d0';
                dot3.style.color = '#d0d0d0';
                break;
            case 1:
                dot1.style.color = '#666666';
                dot2.style.color = '#666666';
                dot3.style.color = '#d0d0d0';
                break;
            case 2:
                dot1.style.color = '#666666';
                dot2.style.color = '#666666';
                dot3.style.color = '#666666';
                break;
        }
        dotDanceStage++;
        if (dotDanceStage == 3)
        {
            dotDanceStage = 0;
        }
        setTimeout('loadingBarDotDance()', 500);
    }
}

function showLoadingBar()
{
    if (!loadingBarShown) return;
    var obj = document.getElementById('loadingBar');
    if (obj)
    {
        obj.style.visibility = 'visible';
        loadingBarDotDance();
    }
}

function hideLoadingBar()
{
    var obj = document.getElementById('loadingBar');
    loadingBarShown = false;
    if (obj)
    {
        obj.style.visibility = 'hidden';
    }
}
