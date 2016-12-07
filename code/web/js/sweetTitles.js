/*
 * Sweet Titles (c) Creative Commons 2005
 * http://creativecommons.org/licenses/by-sa/2.5/
 * Author: Dustin Diaz | http://www.dustindiaz.com
 *
 * Some modifications by Cognizo Technologies, Inc.
 * Cognizo does not wish to hold a copyright on these modifications.
 *
 * Original author of the addEvent() code unknown; if you know, let us know.
 *
 * $Id: sweetTitles.js 754 2006-09-05 11:31:36Z will $
 */

var sweetTitles = {
    xCord : 0,                              // @Number: X pixel value of current cursor position.
    yCord : 0,                              // @Number: Y pixel value of current cursor position.
    tipElements : [ 'a', 'div', 'img' ],    // @Array: Allowable elements that can have the toolTip.
    obj : Object,                           // @Element: That of which you're hovering over.
    tip : Object,                           // @Element: The actual toolTip itself.
    active : 0,                             // @Number: 0: Not Active || 1: Active
    init : function()
    {
        if (!document.getElementById || !document.createElement ||
            !document.getElementsByTagName)
        {
            return;
        }

        var i, j;
        this.tip = document.createElement('div');
        this.tip.id = 'toolTip';
        document.getElementsByTagName('body')[0].appendChild(this.tip);
        this.tip.style.top = '0';
        this.tip.style.visibility = 'hidden';
        var tipLen = this.tipElements.length;

        for (i = 0; i < tipLen; i++)
        {
            var current = document.getElementsByTagName(this.tipElements[i]);
            var curLen = current.length;
            for (j = 0; j < curLen; j++)
            {
                if (current[j].title)
                {
                    addEvent(current[j], 'mouseover', this.tipOver);
                    addEvent(current[j], 'mouseout', this.tipOut);
                    current[j].setAttribute('tip', current[j].title);
                    current[j].removeAttribute('title');
                }
            }
        }
    },
    updateXY : function(e)
    {
        if (document.captureEvents)
        {
            sweetTitles.xCord = e.pageX;
            sweetTitles.yCord = e.pageY;
        }
        else if (window.event.clientX)
        {
            sweetTitles.xCord = window.event.clientX + document.documentElement.scrollLeft;
            sweetTitles.yCord = window.event.clientY + document.documentElement.scrollTop;
        }
    },
    tipOut: function()
    {
        if (window.tID)
        {
            clearTimeout(tID);
        }
        if (window.opacityID)
        {
            clearTimeout(opacityID);
        }

        sweetTitles.tip.style.visibility = 'hidden';
    },
    checkNode : function()
    {
        var trueObj = this.obj;

        if (this.tipElements.inArray(trueObj.nodeName.toLowerCase()))
        {
            return trueObj;
        }

        return trueObj.parentNode;
    },
    tipOver : function(e)
    {
        sweetTitles.obj = this;
        tID = window.setTimeout('sweetTitles.tipShow()', 500);
        sweetTitles.updateXY(e);
    },
    tipShow : function()
    {
        var tp = Number(this.yCord) + 15;
        var lt = Number(this.xCord) + 10;
        var anch = this.checkNode();

        this.tip.innerHTML = '<p>' + anch.getAttribute('tip');
        if (document.documentElement.clientWidth + document.documentElement.scrollLeft
            < (this.tip.offsetWidth + lt))
        {
            this.tip.style.left = (lt - (this.tip.offsetWidth + 10)) + 'px';
        }
        else
        {
            this.tip.style.left = lt + 'px';
        }

        if (document.documentElement.clientHeight + document.documentElement.scrollTop
            < (this.tip.offsetHeight + tp))
        {
            this.tip.style.top = (tp - (this.tip.offsetHeight + 10)) + 'px';
        }
        else
        {
            this.tip.style.top = tp + 'px';
        }

        this.tip.style.visibility = 'visible';
        this.tip.style.opacity = '1';
        this.tipFade(50);
    },
    tipFade: function(opac)
    {
        var newOpac = opac + 10;

        if (newOpac < 100)
        {
            this.tip.style.opacity = '.' + newOpac;
            this.tip.style.filter = 'alpha(opacity:' + newOpac + ')';
            opacityID = window.setTimeout("sweetTitles.tipFade('" + newOpac + "')", 20);
        }
        else
        {
            this.tip.style.opacity = '100';
            this.tip.style.filter = 'alpha(opacity:100)';
        }
    }
};

function pageLoader()
{
    sweetTitles.init();
}

addEvent(window, 'load', pageLoader, false);
addEvent(window, 'unload', EventCache.flush, false);
