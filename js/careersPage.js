// JavaScript Document -- CATS Careers Page
var returnToMainOn = new Image(130, 25);
returnToMainOn.src = 'images/careers_return-o.gif';
var returnToMainOff = new Image(130, 25);
returnToMainOff.src = 'images/careers_return.gif';
var showAllJobsOn = new Image(130, 25);
showAllJobsOn.src = 'images/careers_show-o.gif';
var showAllJobsOff = new Image(130, 25);
showAllJobsOff.src = 'images/careers_show.gif';
var searchJobsOn = new Image(130, 25);
searchJobsOn.src = 'images/careers_search-o.gif';
var searchJobsOff = new Image(130, 25);
searchJobsOff.src = 'images/careers_search.gif';
var rssFeedOn = new Image(130, 25);
rssFeedOn.src = 'images/careers_rss-o.gif';
var rssFeedOff = new Image(130, 25);
rssFeedOff.src = 'images/careers_rss.gif';
var applyToPositionOn = new Image(130, 25);
applyToPositionOn.src = 'images/careers_apply-o.gif';
var applyToPositionOff = new Image(130, 25);
applyToPositionOff.src = 'images/careers_apply.gif';
var shareWithFriendOn = new Image(130, 25);
shareWithFriendOn.src = 'images/careers_share-o.gif';
var shareWithFriendOff = new Image(130, 25);
shareWithFriendOff.src = 'images/careers_share.gif';
var submitApplicationNowOn = new Image(195, 25);
submitApplicationNowOn.src = 'images/careers_submit-o.gif';
var submitApplicationNowOff = new Image(195, 25);
submitApplicationNowOff.src = 'images/careers_submit.gif';

function buttonMouseOver(txt, tf)
{
var newImage;
var obj = document.getElementById(txt);
var helpObj = document.getElementById('buttonHelpText');
if (tf) newImage = eval(txt + 'On');
else newImage = eval(txt + 'Off');
if (obj)
{
obj.src = newImage.src;
if (obj = document.getElementById(txt + 'Text'))
{
if (tf) helpObj.innerHTML = obj.innerHTML;
else helpObj.innerHTML = '&nbsp;';
}
}
return false;
}