/***********************************************
 Fool-Proof Date Input Script with DHTML Calendar
 by Jason Moon - calendar@moonscript.com
 ************************************************/

// Customizable variables
var DefaultDateFormat = 'MM-DD-YY'; // If no date format is supplied, this will be used instead
var HideWait = 1; // Number of seconds before the calendar will disappear
var Y2kPivotPoint = 76; // 2-digit years before this point will be created in the 21st century
var FontSize = 10; // In pixels
var FontFamily = 'Tahoma';
var CellWidth = 18;
var CellHeight = 16;
var ImageURL = 'images/calendar.gif';
var NextURL = 'images/next.gif';
var PrevURL = 'images/prev.gif';
var CalBGColor = 'white';
var TopRowBGColor = 'buttonface';
var DayBGColor = 'lightgrey';

// Global variables
var ZCounter = 100;
var Today = new Date();
var WeekDays = new Array('S','M','T','W','T','F','S');
var MonthDays = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
var MonthNames = new Array('January','February','March','April','May','June','July','August','September','October','November','December');

// Write out the stylesheet definition for the calendar
with (document) {
   writeln('<style>');
   writeln('td.calendarDateInput {letter-spacing:normal;line-height:normal;font-family:' + FontFamily + ',Sans-Serif;font-size:' + FontSize + 'px;}');
   writeln('select.calendarDateInput {letter-spacing:.06em;font-family:Verdana,Sans-Serif;font-size:11px;}');
   writeln('input.calendarDateInput {letter-spacing:.06em;font-family:Verdana,Sans-Serif;font-size:11px;}');
   writeln('</style>');
}

// Only allows certain keys to be used in the date field
function YearDigitsOnly(e)
{
    if (!e)
    {
        var e = window.event;
    }

    if (typeof(window.event) != 'undefined')
    {
        var KeyCode = e.keyCode;
    }
    else
    {
        var KeyCode = e.which;
    }

    return ((KeyCode == 8) // backspace
        || (KeyCode == 9) // tab
        || (KeyCode == 37) // left arrow
        || (KeyCode == 39) // right arrow
        || (KeyCode == 46) // delete
        || ((KeyCode > 47) && (KeyCode < 58)) // 0 - 9
   );
}

// Gets the absolute pixel position of the supplied element
function GetTagPixels(StartTag, Direction) {
   var PixelAmt = (Direction == 'LEFT') ? StartTag.offsetLeft : StartTag.offsetTop;
   while ((StartTag.tagName != 'BODY') && (StartTag.tagName != 'HTML')) {
      StartTag = StartTag.offsetParent;
      PixelAmt += (Direction == 'LEFT') ? StartTag.offsetLeft : StartTag.offsetTop;
   }
   return PixelAmt;
}

// Is the specified select-list behind the calendar?
function BehindCal(SelectList, CalLeftX, CalRightX, CalTopY, CalBottomY, ListTopY) {
   var ListLeftX = GetTagPixels(SelectList, 'LEFT');
   var ListRightX = ListLeftX + SelectList.offsetWidth;
   var ListBottomY = ListTopY + SelectList.offsetHeight;
   return (((ListTopY < CalBottomY) && (ListBottomY > CalTopY)) && ((ListLeftX < CalRightX) && (ListRightX > CalLeftX)));
}

// For IE, hides any select-lists that are behind the calendar
function FixSelectLists(Over) {
   if (navigator.appName == 'Microsoft Internet Explorer') {
      var CalDiv = this.getCalendar();
      var CalLeftX = CalDiv.offsetLeft;
      var CalRightX = CalLeftX + CalDiv.offsetWidth;
      var CalTopY = CalDiv.offsetTop;
      var CalBottomY = CalTopY + (CellHeight * 9);
      var FoundCalInput = false;
      formLoop :
      for (var j=this.formNumber;j<document.forms.length;j++) {
         for (var i=0;i<document.forms[j].elements.length;i++) {
            if (typeof document.forms[j].elements[i].type == 'string') {
               if ((document.forms[j].elements[i].type == 'hidden') && (document.forms[j].elements[i].name == this.hiddenFieldName)) {
                  FoundCalInput = true;
                  i += 3; // 3 elements between the 1st hidden field and the last year input field
               }
               if (FoundCalInput) {
                  if (document.forms[j].elements[i].type.substr(0,6) == 'select') {
                     ListTopY = GetTagPixels(document.forms[j].elements[i], 'TOP');
                     if (ListTopY < CalBottomY) {
                        if (BehindCal(document.forms[j].elements[i], CalLeftX, CalRightX, CalTopY, CalBottomY, ListTopY)) {
                           document.forms[j].elements[i].style.visibility = (Over) ? 'hidden' : 'visible';
                        }
                     }
                     else break formLoop;
                  }
               }
            }
         }
      }
   }
}

// Displays a message in the status bar when hovering over the calendar days
function DayCellHover(Cell, Over, Color, HoveredDay) {
   Cell.style.backgroundColor = (Over) ? DayBGColor : Color;
   if (Over) {
      if ((this.yearValue == Today.getFullYear()) && (this.monthIndex == Today.getMonth()) && (HoveredDay == Today.getDate())) self.status = 'Click to select today';
      else {
         var Suffix = HoveredDay.toString();
         switch (Suffix.substr(Suffix.length - 1, 1)) {
            case '1' : Suffix += (HoveredDay == 11) ? 'th' : 'st'; break;
            case '2' : Suffix += (HoveredDay == 12) ? 'th' : 'nd'; break;
            case '3' : Suffix += (HoveredDay == 13) ? 'th' : 'rd'; break;
            default : Suffix += 'th'; break;
         }
         self.status = 'Click to select ' + this.monthName + ' ' + Suffix;
      }
   }
   else self.status = '';
   return true;
}

// Sets the form elements after a day has been picked from the calendar
function PickDisplayDay(ClickedDay) {
   this.show();
   var MonthList = this.getMonthList();
   var DayList = this.getDayList();
   var YearField = this.getYearField();
   FixDayList(DayList, GetDayCount(this.displayed.yearValue, this.displayed.monthIndex));
   // Select the month and day in the lists
   for (var i=0;i<MonthList.length;i++) {
      if (MonthList.options[i].value == this.displayed.monthIndex) MonthList.options[i].selected = true;
   }
   for (var j=1;j<=DayList.length;j++) {
      if (j == ClickedDay) DayList.options[j-1].selected = true;
   }
   this.setPicked(this.displayed.yearValue, this.displayed.monthIndex, ClickedDay);
   // Change the year, if necessary
   YearField.value = this.picked.yearPad;
   YearField.defaultValue = YearField.value;
}

// Builds the HTML for the calendar days
function BuildCalendarDays() {
   var Rows = 5;
   if (((this.displayed.dayCount == 31) && (this.displayed.firstDay > 4)) || ((this.displayed.dayCount == 30) && (this.displayed.firstDay == 6))) Rows = 6;
   else if ((this.displayed.dayCount == 28) && (this.displayed.firstDay == 0)) Rows = 4;
   var HTML = '<table cellspacing="0" cellpadding="0" style="cursor:default">';
   for (var j=0;j<Rows;j++) {
      HTML += '<tr>';
      for (var i=1;i<=7;i++) {
         Day = (j * 7) + (i - this.displayed.firstDay);
         if ((Day >= 1) && (Day <= this.displayed.dayCount)) {
            if ((this.displayed.yearValue == this.picked.yearValue) && (this.displayed.monthIndex == this.picked.monthIndex) && (Day == this.picked.day)) {
               TextStyle = 'color:white;font-weight:bold;'
               BackColor = DayBGColor;
            }
            else {
               TextStyle = 'color:black;'
               BackColor = CalBGColor;
            }
            if ((this.displayed.yearValue == Today.getFullYear()) && (this.displayed.monthIndex == Today.getMonth()) && (Day == Today.getDate())) TextStyle += 'border:1px solid darkred;padding:0px;';
            HTML += '<td align="center" class="calendarDateInput" style="cursor:default;height:' + CellHeight + ';width:' + CellWidth + ';' + TextStyle + ';background-color:' + BackColor + '" onclick="' + this.objName + '.pickDay(' + Day + ')" onmouseover="return ' + this.objName + '.displayed.dayHover(this,true,\'' + BackColor + '\',' + Day + ')" onmouseout="return ' + this.objName + '.displayed.dayHover(this,false,\'' + BackColor + '\')">' + Day + '</td>';
         }
         else HTML += '<td class="calendarDateInput" style="height:' + CellHeight + '">&nbsp;</td>';
      }
      HTML += '</tr>';
   }
   return HTML += '</table>';
}

// Determines which century to use (20th or 21st) when dealing with 2-digit years
function GetGoodYear(YearDigits) {
   if (YearDigits.length == 4) return YearDigits;
   else {
      /*var Millennium = (YearDigits < Y2kPivotPoint) ? 2000 : 1900;
      return Millennium + parseInt(YearDigits,10);*/
      return 2000 + parseInt(YearDigits,10);
   }
}

// Returns the number of days in a month (handles leap-years)
function GetDayCount(SomeYear, SomeMonth) {
   return ((SomeMonth == 1) && ((SomeYear % 400 == 0) || ((SomeYear % 4 == 0) && (SomeYear % 100 != 0)))) ? 29 : MonthDays[SomeMonth];
}

// Highlights the buttons
function VirtualButton(Cell, ButtonDown) {
   if (ButtonDown) {
      Cell.style.borderLeft = 'buttonshadow 1px solid';
      Cell.style.borderTop = 'buttonshadow 1px solid';
      Cell.style.borderBottom = 'buttonhighlight 1px solid';
      Cell.style.borderRight = 'buttonhighlight 1px solid';
   }
   else {
      Cell.style.borderLeft = 'buttonhighlight 1px solid';
      Cell.style.borderTop = 'buttonhighlight 1px solid';
      Cell.style.borderBottom = 'buttonshadow 1px solid';
      Cell.style.borderRight = 'buttonshadow 1px solid';
   }
}

// Mouse-over for the previous/next month buttons
function NeighborHover(Cell, Over, DateObj) {
   if (Over) {
      VirtualButton(Cell, false);
      self.status = 'Click to view ' + DateObj.fullName;
   }
   else {
      Cell.style.border = 'buttonface 1px solid';
      self.status = '';
   }
   return true;
}

// Adds/removes days from the day list, depending on the month/year
function FixDayList(DayList, NewDays) {
   var DayPick = DayList.selectedIndex + 1;
   if (NewDays != DayList.length) {
      var OldSize = DayList.length;
      for (var k=Math.min(NewDays,OldSize);k<Math.max(NewDays,OldSize);k++) {
         (k >= NewDays) ? DayList.options[NewDays] = null : DayList.options[k] = new Option(k+1, k+1);
      }
      DayPick = Math.min(DayPick, NewDays);
      DayList.options[DayPick-1].selected = true;
   }
   return DayPick;
}

// Resets the year to its previous valid value when something invalid is entered
function FixYearInput(YearField) {
   var YearRE = new RegExp('\\d{' + YearField.defaultValue.length + '}');
   if (!YearRE.test(YearField.value)) YearField.value = YearField.defaultValue;
}

// Displays a message in the status bar when hovering over the calendar icon
function CalIconHover(Over) {
   var Message = (this.isShowing()) ? 'hide' : 'show';
   self.status = (Over) ? 'Click to ' + Message + ' the calendar' : '';
   return true;
}

// Starts the timer over from scratch
function CalTimerReset() {
   eval('clearTimeout(' + this.timerID + ')');
   eval(this.timerID + '=setTimeout(\'' + this.objName + '.show()\',' + (HideWait * 1000) + ')');
}

// The timer for the calendar
function DoTimer(CancelTimer) {
   if (CancelTimer) eval('clearTimeout(' + this.timerID + ')');
   else {
      eval(this.timerID + '=null');
      this.resetTimer();
   }
}

// Show or hide the calendar
function ShowCalendar() {
   if (this.isShowing()) {
      var StopTimer = true;
      this.getCalendar().style.zIndex = --ZCounter;
      this.getCalendar().style.visibility = 'hidden';
      this.fixSelects(false);
   }
   else {
      var StopTimer = false;
      this.fixSelects(true);
      this.getCalendar().style.zIndex = ++ZCounter;
      this.getCalendar().style.visibility = 'visible';
   }
   this.handleTimer(StopTimer);
   self.status = '';
}

// Hides the input elements when the "blank" month is selected
function SetElementStatus(Hide) {
   this.getDayList().style.visibility = (Hide) ? 'hidden' : 'visible';
   this.getYearField().style.visibility = (Hide) ? 'hidden' : 'visible';
   this.getCalendarLink().style.visibility = (Hide) ? 'hidden' : 'visible';
}

// Sets the date, based on the month selected
function CheckMonthChange(MonthList) {
   var DayList = this.getDayList();
   if (MonthList.options[MonthList.selectedIndex].value == '') {
      DayList.selectedIndex = 0;
      this.hideElements(true);
      this.setHidden('');
   }
   else {
      this.hideElements(false);
      if (this.isShowing()) {
         this.resetTimer(); // Gives the user more time to view the calendar with the newly-selected month
         this.getCalendar().style.zIndex = ++ZCounter; // Make sure this calendar is on top of any other calendars
      }
      var DayPick = FixDayList(DayList, GetDayCount(this.picked.yearValue, MonthList.options[MonthList.selectedIndex].value));
      this.setPicked(this.picked.yearValue, MonthList.options[MonthList.selectedIndex].value, DayPick);
   }
}

// Sets the date, based on the day selected
function CheckDayChange(DayList) {
   if (this.isShowing()) this.show();
   this.setPicked(this.picked.yearValue, this.picked.monthIndex, DayList.selectedIndex+1);
}

// Changes the date when a valid year has been entered
function CheckYearInput(YearField) {
   if ((YearField.value.length == YearField.defaultValue.length) && (YearField.defaultValue != YearField.value)) {
      if (this.isShowing()) {
         this.resetTimer(); // Gives the user more time to view the calendar with the newly-entered year
         this.getCalendar().style.zIndex = ++ZCounter; // Make sure this calendar is on top of any other calendars
      }
      var NewYear = GetGoodYear(YearField.value);
      var MonthList = this.getMonthList();
      var NewDay = FixDayList(this.getDayList(), GetDayCount(NewYear, this.picked.monthIndex));
      this.setPicked(NewYear, this.picked.monthIndex, NewDay);
      YearField.defaultValue = YearField.value;
   }
}

// Holds characteristics about a date
function dateObject() {
   if (Function.call) { // Used when 'call' method of the Function object is supported
      var ParentObject = this;
      var ArgumentStart = 0;
   }
   else { // Used with 'call' method of the Function object is NOT supported
      var ParentObject = arguments[0];
      var ArgumentStart = 1;
   }

	if (arguments.length == (ArgumentStart+1))
	{
		//alert('A');
		ParentObject.date = new Date(arguments[ArgumentStart+0]);
	}
	else
	{
		//alert('B - ' + arguments[ArgumentStart+0]);
		ParentObject.date = new Date(arguments[ArgumentStart+0], arguments[ArgumentStart+1], arguments[ArgumentStart+2]);
	}
	
   ParentObject.yearValue = ParentObject.date.getFullYear();
   if (ParentObject.yearValue < 1995) ParentObject.yearValue += 100;
   ParentObject.monthIndex = ParentObject.date.getMonth();

   ParentObject.monthName = MonthNames[ParentObject.monthIndex];
   ParentObject.fullName = ParentObject.monthName + ' ' + ParentObject.yearValue;
   ParentObject.day = ParentObject.date.getDate();
   ParentObject.dayCount = GetDayCount(ParentObject.yearValue, ParentObject.monthIndex);
   var FirstDate = new Date(ParentObject.yearValue, ParentObject.monthIndex, 1);
   ParentObject.firstDay = FirstDate.getDay();
}

// Keeps track of the date that goes into the hidden field
function storedMonthObject(DateFormat, DateYear, DateMonth, DateDay) {
	//alert ('smo - ' + DateYear);
   (Function.call) ? dateObject.call(this, DateYear, DateMonth, DateDay) : dateObject(this, DateYear, DateMonth, DateDay);
   this.yearPad = this.yearValue.toString();
   this.monthPad = (this.monthIndex < 9) ? '0' + String(this.monthIndex + 1) : this.monthIndex + 1;
   this.dayPad = (this.day < 10) ? '0' + this.day.toString() : this.day;

   this.monthShort = this.monthName.substr(0,3).toUpperCase();
   // Formats the year with 2 digits instead of 4
   if (DateFormat.indexOf('YYYY') == -1) this.yearPad = this.yearPad.substr(2);
   // Define the date-part delimiter
   if (DateFormat.indexOf('/') >= 0) var Delimiter = '/';
   else if (DateFormat.indexOf('-') >= 0) var Delimiter = '-';
   else var Delimiter = '';
   // Determine the order of the months and days
   if (/DD?.?((MON)|(MM?M?))/.test(DateFormat)) {
      this.formatted = this.dayPad + Delimiter;
      this.formatted += (RegExp.$1.length == 3) ? this.monthShort : this.monthPad;
   }
   else if (/((MON)|(MM?M?))?.?DD?/.test(DateFormat)) {
      this.formatted = (RegExp.$1.length == 3) ? this.monthShort : this.monthPad;
      this.formatted += Delimiter + this.dayPad;
   }
   // Either prepend or append the year to the formatted date
   this.formatted = (DateFormat.substr(0,2) == 'YY') ? this.yearPad + Delimiter + this.formatted : this.formatted + Delimiter + this.yearPad;
}

// Object for the current displayed month
function displayMonthObject(ParentObject, DateYear, DateMonth, DateDay) {
   (Function.call) ? dateObject.call(this, DateYear, DateMonth, DateDay) : dateObject(this, DateYear, DateMonth, DateDay);
   this.displayID = ParentObject.hiddenFieldName + '_Current_ID';
   this.getDisplay = new Function('return document.getElementById(this.displayID)');
   this.dayHover = DayCellHover;
   this.goCurrent = new Function(ParentObject.objName + '.getCalendar().style.zIndex=++ZCounter;' + ParentObject.objName + '.setDisplayed(Today.getFullYear(),Today.getMonth());');
   if (ParentObject.formNumber >= 0) this.getDisplay().innerHTML = this.fullName;
}

// Object for the previous/next buttons
function neighborMonthObject(ParentObject, IDText, DateMS) {
   (Function.call) ? dateObject.call(this, DateMS) : dateObject(this, DateMS);
   this.buttonID = ParentObject.hiddenFieldName + '_' + IDText + '_ID';
   this.hover = new Function('C','O','NeighborHover(C,O,this)');
   this.getButton = new Function('return document.getElementById(this.buttonID)');
   this.go = new Function(ParentObject.objName + '.getCalendar().style.zIndex=++ZCounter;' + ParentObject.objName + '.setDisplayed(this.yearValue,this.monthIndex);');
   if (ParentObject.formNumber >= 0) this.getButton().title = this.monthName;
}

// Sets the currently-displayed month object
function SetDisplayedMonth(DispYear, DispMonth) {
   this.displayed = new displayMonthObject(this, DispYear, DispMonth, 1);
   // Creates the previous and next month objects
   this.previous = new neighborMonthObject(this, 'Previous', this.displayed.date.getTime() - 86400000);
   this.next = new neighborMonthObject(this, 'Next', this.displayed.date.getTime() + (86400000 * (this.displayed.dayCount + 1)));
   // Creates the HTML for the calendar
   if (this.formNumber >= 0) this.getDayTable().innerHTML = this.buildCalendar();
}

// Sets the current selected date
function SetPickedMonth(PickedYear, PickedMonth, PickedDay) {
//alert('spm - ' + PickedYear);
   this.picked = new storedMonthObject(this.format, PickedYear, PickedMonth, PickedDay);
   this.setHidden(this.picked.formatted);
   this.setDisplayed(PickedYear, PickedMonth);
}

// The calendar object
function calendarObject(DateName, DateFormat, DefaultDate) {

   /* Properties */
   this.hiddenFieldName = DateName;
   this.monthListID = DateName + '_Month_ID';
   this.dayListID = DateName + '_Day_ID';
   this.yearFieldID = DateName + '_Year_ID';
   this.monthDisplayID = DateName + '_Current_ID';
   this.calendarID = DateName + '_ID';
   this.dayTableID = DateName + '_DayTable_ID';
   this.calendarLinkID = this.calendarID + '_Link';
   this.timerID = this.calendarID + '_Timer';
   this.objName = DateName + '_Object';
   this.format = DateFormat;
   this.formNumber = -1;
   this.picked = null;
   this.displayed = null;
   this.previous = null;
   this.next = null;

   /* Methods */
   this.setPicked = SetPickedMonth;
   this.setDisplayed = SetDisplayedMonth;
   this.checkYear = CheckYearInput;
   this.fixYear = FixYearInput;
   this.changeMonth = CheckMonthChange;
   this.changeDay = CheckDayChange;
   this.resetTimer = CalTimerReset;
   this.hideElements = SetElementStatus;
   this.show = ShowCalendar;
   this.handleTimer = DoTimer;
   this.iconHover = CalIconHover;
   this.buildCalendar = BuildCalendarDays;
   this.pickDay = PickDisplayDay;
   this.fixSelects = FixSelectLists;
   this.setHidden = new Function('D','if (this.formNumber >= 0 || this.formNumber == -99) this.getHiddenField().value=D');
   // Returns a reference to these elements
   this.getHiddenField = new Function('if (this.formNumber >= 0) return document.forms[this.formNumber].elements[this.hiddenFieldName]; else if (this.formNumber == -99) return document.getElementById(this.hiddenFieldName);');
   this.getMonthList = new Function('return document.getElementById(this.monthListID)');
   this.getDayList = new Function('return document.getElementById(this.dayListID)');
   this.getYearField = new Function('return document.getElementById(this.yearFieldID)');
   this.getCalendar = new Function('return document.getElementById(this.calendarID)');
   this.getDayTable = new Function('return document.getElementById(this.dayTableID)');
   this.getCalendarLink = new Function('return document.getElementById(this.calendarLinkID)');
   this.getMonthDisplay = new Function('return document.getElementById(this.monthDisplayID)');
   this.isShowing = new Function('return !(this.getCalendar().style.visibility != \'visible\')');

   /* Constructor */
   // Functions used only by the constructor
   function getMonthIndex(MonthAbbr) { // Returns the index (0-11) of the supplied month abbreviation
      for (var MonPos=0;MonPos<MonthNames.length;MonPos++) {
         if (MonthNames[MonPos].substr(0,3).toUpperCase() == MonthAbbr.toUpperCase()) break;
      }
      return MonPos;
   }
   function SetGoodDate(CalObj, Notify) { // Notifies the user about their bad default date, and sets the current system date
      CalObj.setPicked(Today.getFullYear(), Today.getMonth(), Today.getDate());
      if (Notify) alert('WARNING: The supplied date is not in valid \'' + DateFormat + '\' format: ' + DefaultDate + '.\nTherefore, the current system date will be used instead: ' + CalObj.picked.formatted);
   }
   // Main part of the constructor
   if (DefaultDate != '') {
      if ((this.format == 'YYYYMMDD') && (/^(\d{4})(\d{2})(\d{2})$/.test(DefaultDate))) {
         this.setPicked(parseInt(RegExp.$1, 10), parseInt(RegExp.$2, 10)-1, parseInt(RegExp.$3, 10));
      } else {
         // Get the year
         if ((this.format.substr(0,2) == 'YY') && (/^(\d{2,4})(-|\/)/.test(DefaultDate))) { // Year is at the beginning
            var YearPart = parseInt(GetGoodYear(RegExp.$1), 10);
            // Determine the order of the months and days
            if (/(-|\/)(\w{1,3})(-|\/)(\w{1,3})$/.test(DefaultDate)) {
               var MidPart = RegExp.$2;
               var EndPart = RegExp.$4;
               if (/D$/.test(this.format)) { // Ends with days
                  var DayPart = EndPart;
                  var MonthPart = MidPart;
               }
               else {
                  var DayPart = MidPart;
                  var MonthPart = EndPart;
               }
               MonthPart = (/\d{1,2}/i.test(MonthPart)) ? parseInt(MonthPart, 10) - 1 : getMonthIndex(MonthPart);
               this.setPicked(YearPart, MonthPart, parseInt(DayPart, 10));
            }
            else SetGoodDate(this, true);
         }
         else if (/(-|\/)(\d{2,4})$/.test(DefaultDate)) { // Year is at the end
            var YearPart = parseInt(GetGoodYear(RegExp.$2), 10);
            // Determine the order of the months and days
            if (/^(\w{1,3})(-|\/)(\w{1,3})(-|\/)/.test(DefaultDate)) {
               if (this.format.substr(0,1) == 'D') { // Starts with days
                  var DayPart = RegExp.$1;
                  var MonthPart = RegExp.$3;
               }
               else { // Starts with months
                  var MonthPart = RegExp.$1;
                  var DayPart = RegExp.$3;
               }
               MonthPart = (/\d{1,2}/i.test(MonthPart)) ? parseInt(MonthPart, 10) - 1 : getMonthIndex(MonthPart);
               this.setPicked(YearPart, MonthPart, parseInt(DayPart, 10));
            }
            else SetGoodDate(this, true);
         }
         else SetGoodDate(this, true);
      }
   }
}

function DateInput(DateName, Required, DateFormat, DefaultDate, TabIndex)
{
    var CurrentDate = new storedMonthObject(
        DateFormat, Today.getFullYear(), Today.getMonth(), Today.getDate()
    );

    DateFormat = DateFormat.toUpperCase();

    if (!(/^(Y{2,4}(-|\/)?)?((MON)|(MM?M?)|(DD?))(-|\/)?((MON)|(MM?M?)|(DD?))((-|\/)Y{2,4})?$/i.test(DateFormat)))
    {
        alert(
            'Error: The specified date format for the \'' + DateName +
            '\' field, \'' + DateFormat + '\', is invalid.'
        );
        return;
    }

    /* If DefaultDate is required but not specified, use today's date. */
    if (DefaultDate == '' && Required)
    {
        DefaultDate = CurrentDate.formatted;
    }

    /* Create the Calendar object. */
    eval(
        DateName + '_Object = new calendarObject(\'' + DateName +
        '\',\'' + DateFormat + '\',\'' + DefaultDate + '\')'
    );

    /* Get a reference to the object to avoid more ugly eval() hacks. */
    var objectName = DateName + '_Object';
    eval('var object = ' + DateName + '_Object;');

    /* Determine initial state of day and year inputs and the calendar icon. */
    if (Required || DefaultDate != '')
    {
        var initialStatus = '';
        var initialDate = object.picked.formatted;
    }
    else
    {
        var initialStatus = ' style="visibility:hidden"';
        var initialDate = '';
        object.setPicked(Today.getFullYear(), Today.getMonth(), Today.getDate());
    }

    /* Calculate tab indexes. */
    if (TabIndex != -1)
    {
        var tabIndexA = ' tabindex="' + TabIndex + '"';
        var tabIndexB = ' tabindex="' + (TabIndex + 1) + '"';
        var tabIndexC = ' tabindex="' + (TabIndex + 2) + '"';
    }
    else
    {
        var tabIndexA = '';
        var tabIndexB = '';
        var tabIndexC = '';
    }

    /* Create form elements; etc. */
    with (document)
    {
        writeln('<input type="hidden" name="' + DateName + '" value="' + initialDate + '" />');

        /* Find the form number of the form we are in. */
        for (var f = 0; f < forms.length; f++)
        {
            for (var e = 0; e < forms[f].elements.length; e++)
            {
                if (typeof(forms[f].elements[e].type) == 'string' &&
                    forms[f].elements[e].type == 'hidden' &&
                    forms[f].elements[e].name == DateName)
                {
                    object.formNumber = f;
                    break;
                }
            }
        }

        writeln('<table style="padding: 0px; border-spacing: 0px; margin: 0px;">');
        writeln('<tr>');

        writeln('<td style="padding: 0px 3px 0px 0px; margin: 0px;">');
        writeln('<select' + tabIndexA + ' class="calendarDateInput" id="' + DateName + '_Month_ID" onchange="' + objectName + '.changeMonth(this);">');

        if (!Required)
        {
            if (DefaultDate == '')
            {
                writeln('<option selected="selected" value="">None</option>');
            }
            else
            {
                writeln('<option value="">None</option>');
            }
        }

        for (var i = 0; i < 12; i++)
        {
            if (object.picked.monthIndex == i && DefaultDate != '')
            {
                MonthSelected = ' selected="selected"';
            }
            else
            {
                MonthSelected = '';
            }

            writeln('<option value="' + i + '"' + MonthSelected + '>' + MonthNames[i].substr(0, 3) + '</option>');
        }

        writeln('</select>');
        writeln('</td>');

        writeln('<td style="padding: 0px 3px 0px 0px; margin: 0px;">');
        writeln('<select' + tabIndexB + initialStatus + ' class="calendarDateInput" id="' + DateName + '_Day_ID" onchange="' + objectName + '.changeDay(this);">');

        for (var j = 1; j <= 31; j++)
        {
            if (object.picked.day == j && DefaultDate != '')
            {
                DaySelected = ' selected="selected"';
            }
            else
            {
                DaySelected = '';
            }

            writeln('<option value="' + j + '"' + DaySelected + '>' + j + '</option>');
        }

        writeln('</select>');
        writeln('</td>');

        writeln('<td style="padding: 0px 3px 0px 0px; margin: 0px;">');
        writeln('<input' + tabIndexC + initialStatus + ' class="calendarDateInput" type="text" id="' + DateName + '_Year_ID" size="' + object.picked.yearPad.length + '" maxlength="' + object.picked.yearPad.length + '" title="Year" value="' + object.picked.yearPad + '" onKeyPress="return YearDigitsOnly(event);" onkeyup="' + objectName + '.checkYear(this);" onBlur="' + objectName + '.fixYear(this);" />');
        writeln('<td style="padding: 0px 3px 0px 0px; margin: 0px;"><a' + initialStatus + ' id="' + DateName + '_ID_Link" href="javascript:' + objectName + '.show();" onmouseover="return ' + objectName + '.iconHover(true);" onmouseout="return ' + objectName + '.iconHover(false);"><img src="' + ImageURL + '" style="vertical-align: middle; border: none;" title="Calendar" /></a>&nbsp;');

        writeln('<span id="' + DateName + '_ID" style="position: absolute; visibility: hidden; width: ' + (CellWidth * 7) + 'px; background-color: ' + CalBGColor + '; border: 1px solid dimgray;" onmouseover="' + objectName + '.handleTimer(true);" onmouseout="' + objectName + '.handleTimer(false);">');

        writeln('<table width="' + (CellWidth * 7) + '" cellspacing="0" cellpadding="1">');

        writeln('<tr style="background-color:' + TopRowBGColor + ';">');
        writeln('<td id="' + DateName + '_Previous_ID" style="cursor: default;" align="center" class="calendarDateInput" style="height: ' + CellHeight + '" onclick="' + objectName + '.previous.go();" onMouseDown="VirtualButton(this, true);" onMouseUp="VirtualButton(this, false);" onmouseover="return ' + objectName + '.previous.hover(this, true)" onmouseout="return ' + objectName + '.previous.hover(this, false);" title="' + object.previous.monthName + '"><img src="' + PrevURL + '"></td>');
        writeln('<td id="' + DateName + '_Current_ID" style="cursor: pointer;" align="center" class="calendarDateInput" style="height: ' + CellHeight + '" colspan="5" onclick="' + objectName + '.displayed.goCurrent();" onmouseover="self.status=\'Click to view ' + CurrentDate.fullName + '\'; return true;" onmouseout="self.status = \'\'; return true;" title="Show Current Month">' + object.displayed.fullName + '</td>');
        writeln('<td id="' + DateName + '_Next_ID" style="cursor: default;" align="center" class="calendarDateInput" style="height: ' + CellHeight + '" onclick="' + objectName + '.next.go();" onMouseDown="VirtualButton(this, true);" onMouseUp="VirtualButton(this, false);" onmouseover="return ' + objectName + '.next.hover(this, true);" onmouseout="return ' + objectName + '.next.hover(this, false);" title="' + object.next.monthName + '"><img src="' + NextURL + '" /></td>');
        writeln('</tr>');

        writeln('<tr>');
        for (var w = 0; w < 7; w++)
        {
            writeln('<td width="' + CellWidth + '" align="center" class="calendarDateInput" style="height:' + CellHeight + '; width:' + CellWidth + '; font-weight: bold; border-top: 1px solid dimgray; border-bottom: 1px solid dimgray;">' + WeekDays[w] + '</td>');
        }
        writeln('</tr>');

        writeln('</table>');

        writeln('<span id="' + DateName + '_DayTable_ID">' + object.buildCalendar() + '</span>');

        writeln('</span>');

        writeln('</td>');
        writeln('</tr>');
        writeln('</table>');
    }
}

function DateInputForDOM(DateName, Required, DateFormat, DefaultDate, TabIndex)
{
    var CurrentDate = new storedMonthObject(
        DateFormat, Today.getFullYear(), Today.getMonth(), Today.getDate()
    );

    DateFormat = DateFormat.toUpperCase();

    if (!(/^(Y{2,4}(-|\/)?)?((MON)|(MM?M?)|(DD?))(-|\/)?((MON)|(MM?M?)|(DD?))((-|\/)Y{2,4})?$/i.test(DateFormat)))
    {
        alert(
            'Error: The specified date format for the \'' + DateName +
            '\' field, \'' + DateFormat + '\', is invalid.'
        );
        return;
    }

    /* If DefaultDate is required but not specified, use today's date. */
    if (DefaultDate == '' && Required)
    {
        DefaultDate = CurrentDate.formatted;
    }

    /* Create the Calendar object. */
    eval(
        DateName + '_Object = new calendarObject(\'' + DateName +
        '\',\'' + DateFormat + '\',\'' + DefaultDate + '\')'
    );

    /* Get a reference to the object to avoid more ugly eval() hacks. */
    var objectName = DateName + '_Object';
    eval('var object = ' + DateName + '_Object;');

    /* Determine initial state of day and year inputs and the calendar icon. */
    if (Required || DefaultDate != '')
    {
        var initialStatus = '';
        var initialDate = object.picked.formatted;
    }
    else
    {
        var initialStatus = ' style="visibility:hidden"';
        var initialDate = '';
        object.setPicked(Today.getFullYear(), Today.getMonth(), Today.getDate());
    }

    /* Calculate tab indexes. */
    if (TabIndex != -1)
    {
        var tabIndexA = ' tabindex="' + TabIndex + '"';
        var tabIndexB = ' tabindex="' + (TabIndex + 1) + '"';
        var tabIndexC = ' tabindex="' + (TabIndex + 2) + '"';
    }
    else
    {
        var tabIndexA = '';
        var tabIndexB = '';
        var tabIndexC = '';
    }
    

    var outCode = '';

    outCode +=('<input type="hidden" name="' + DateName + '" id="' + DateName + '" value="' + initialDate + '" />');

    /* Flag. */
    object.formNumber = -99;

    outCode += ('<table style="padding: 0px; border-spacing: 0px; margin: 0px;">');
    outCode += ('<tr>');

    outCode += ('<td style="padding: 0px 3px 0px 0px; margin: 0px;">');
    outCode += ('<select' + tabIndexA + ' class="calendarDateInput" id="' + DateName + '_Month_ID" onchange="' + objectName + '.changeMonth(this);">');

    if (!Required)
    {
        if (DefaultDate == '')
        {
            outCode += ('<option selected="selected" value="">None</option>');
        }
        else
        {
            outCode += ('<option value="">None</option>');
        }
    }

    for (var i = 0; i < 12; i++)
    {
        if (object.picked.monthIndex == i && DefaultDate != '')
        {
            MonthSelected = ' selected="selected"';
        }
        else
        {
            MonthSelected = '';
        }

        outCode += ('<option value="' + i + '"' + MonthSelected + '>' + MonthNames[i].substr(0, 3) + '</option>');
    }

    outCode += ('</select>');
    outCode += ('</td>');

    outCode += ('<td style="padding: 0px 3px 0px 0px; margin: 0px;">');
    outCode += ('<select' + tabIndexB + initialStatus + ' class="calendarDateInput" id="' + DateName + '_Day_ID" onchange="' + objectName + '.changeDay(this);">');

    for (var j = 1; j <= 31; j++)
    {
        if (object.picked.day == j && DefaultDate != '')
        {
            DaySelected = ' selected="selected"';
        }
        else
        {
            DaySelected = '';
        }

        outCode += ('<option value="' + j + '"' + DaySelected + '>' + j + '</option>');
    }

    outCode += ('</select>');
    outCode += ('</td>');

    outCode += ('<td style="padding: 0px 3px 0px 0px; margin: 0px;">');
    outCode += ('<input' + tabIndexC + initialStatus + ' class="calendarDateInput" type="text" id="' + DateName + '_Year_ID" size="' + object.picked.yearPad.length + '" maxlength="' + object.picked.yearPad.length + '" title="Year" value="' + object.picked.yearPad + '" onKeyPress="return YearDigitsOnly(event);" onkeyup="' + objectName + '.checkYear(this);" onBlur="' + objectName + '.fixYear(this);" />');
    outCode += ('<td style="padding: 0px 3px 0px 0px; margin: 0px;"><a' + initialStatus + ' id="' + DateName + '_ID_Link" href="javascript:' + objectName + '.show();" onmouseover="return ' + objectName + '.iconHover(true);" onmouseout="return ' + objectName + '.iconHover(false);"><img src="' + ImageURL + '" style="vertical-align: middle; border: none;" title="Calendar" /></a>&nbsp;');

    outCode += ('<span id="' + DateName + '_ID" style="position: absolute; visibility: hidden; width: ' + (CellWidth * 7) + 'px; background-color: ' + CalBGColor + '; border: 1px solid dimgray;" onmouseover="' + objectName + '.handleTimer(true);" onmouseout="' + objectName + '.handleTimer(false);">');

    outCode += ('<table width="' + (CellWidth * 7) + '" cellspacing="0" cellpadding="1">');

    outCode += ('<tr style="background-color:' + TopRowBGColor + ';">');
    outCode += ('<td id="' + DateName + '_Previous_ID" style="cursor: default;" align="center" class="calendarDateInput" style="height: ' + CellHeight + '" onclick="' + objectName + '.previous.go();" onMouseDown="VirtualButton(this, true);" onMouseUp="VirtualButton(this, false);" onmouseover="return ' + objectName + '.previous.hover(this, true)" onmouseout="return ' + objectName + '.previous.hover(this, false);" title="' + object.previous.monthName + '"><img src="' + PrevURL + '"></td>');
    outCode += ('<td id="' + DateName + '_Current_ID" style="cursor: pointer;" align="center" class="calendarDateInput" style="height: ' + CellHeight + '" colspan="5" onclick="' + objectName + '.displayed.goCurrent();" onmouseover="self.status=\'Click to view ' + CurrentDate.fullName + '\'; return true;" onmouseout="self.status = \'\'; return true;" title="Show Current Month">' + object.displayed.fullName + '</td>');
    outCode += ('<td id="' + DateName + '_Next_ID" style="cursor: default;" align="center" class="calendarDateInput" style="height: ' + CellHeight + '" onclick="' + objectName + '.next.go();" onMouseDown="VirtualButton(this, true);" onMouseUp="VirtualButton(this, false);" onmouseover="return ' + objectName + '.next.hover(this, true);" onmouseout="return ' + objectName + '.next.hover(this, false);" title="' + object.next.monthName + '"><img src="' + NextURL + '" /></td>');
    outCode += ('</tr>');

    outCode += ('<tr>');
    for (var w = 0; w < 7; w++)
    {
        outCode += ('<td width="' + CellWidth + '" align="center" class="calendarDateInput" style="height:' + CellHeight + '; width:' + CellWidth + '; font-weight: bold; border-top: 1px solid dimgray; border-bottom: 1px solid dimgray;">' + WeekDays[w] + '</td>');
    }
    outCode += ('</tr>');

    outCode += ('</table>');

    outCode += ('<span id="' + DateName + '_DayTable_ID">' + object.buildCalendar() + '</span>');

    outCode += ('</span>');

    outCode += ('</td>');
    outCode += ('</tr>');
    outCode += ('</table>');
    
    return outCode;
}

function SetDateInputDate(DateName, DateFormat, NewDate)
{
    if (/^(Y{2,4}(-|\/)?)?((MON)|(MM?M?)|(DD?))(-|\/)?((MON)|(MM?M?)|(DD?))((-|\/)Y{2,4})?$/i.test(DateFormat))
    {
        DateFormat = DateFormat.toUpperCase();
    }
    else
    {
        /* Invalid date format. */
        return;
    }

    /* Get a reference to DateName's object. */
    eval('var object = ' + DateName + '_Object;');
	
	var year;
	var month;
	var day;
	
    if (DateFormat == 'YYYYMMDD' && (/^(\d{4})(\d{2})(\d{2})$/.test(NewDate)))
    {
        year = parseInt(RegExp.$1, 10);
        month = parseInt(RegExp.$2, 10);
        day = parseInt(RegExp.$3, 10);

        object.setPicked(year, (month - 1), day);
    }
    else if (DateFormat == 'YYYY-MM-DD' && (/^(\d{4})-(\d{2})-(\d{2})$/.test(NewDate)))
    {
        year = parseInt(RegExp.$1, 10);
        month = parseInt(RegExp.$2, 10);
        day = parseInt(RegExp.$3, 10);

        object.setPicked(year, (month - 1), day);
    }
    else if (DateFormat == 'MM-DD-YY' && (/^(\d{2})-(\d{2})-(\d{2})$/.test(NewDate)))
    {
        year = parseInt(GetGoodYear(RegExp.$3), 10);
        month = parseInt(RegExp.$1, 10);
        day = parseInt(RegExp.$2, 10);

        object.setPicked(year, (month - 1), day);
    }
    else if (DateFormat == 'DD-MM-YY' && (/^(\d{2})-(\d{2})-(\d{2})$/.test(NewDate)))
    {
        year = parseInt(GetGoodYear(RegExp.$3), 10);
        day = parseInt(RegExp.$1, 10);
        month = parseInt(RegExp.$2, 10);

        object.setPicked(year, (month - 1), day);
    }
    else
    {
        /* Invalid date format. */
        return;
    }

    monthList = object.getMonthList();
    dayList = object.getDayList();
    yearField = object.getYearField();

    /* Select the month. */
    for (var i = 0; i < monthList.length; i++)
    {
        if (monthList.options[i].value == (month - 1))
        {
            monthList.selectedIndex = i;
        }
    }

    /* Select the day. */
    dayList.selectedIndex = (day - 1);

    /* Set the year. */
    var yearString = (year % 100) + '';
    if (yearString.length == 1)
    {
        yearString = '0' + yearString;
    }

    yearField.value = yearString;
}
