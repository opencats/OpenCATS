<?php 
//$defaultDate = (isset($args['currentDate']))?$args['currentDate']:date('Y-m-d');
$defaultDate = (isset($args['eventData']))?evArrDflt($args['eventData'],'startDate',date('Y-m-d')):date('Y-m-d');
$defaultView = (isset($args['defaultView']))?$args['defaultView']:'month';
if ($defaultView=='day'){
	$defaultView = 'agendaDay';
} else if ($defaultView=='week'){
	$defaultView = 'agendaWeek';
}

$userId = \E::c('user')->getCurrentUserId();

//vd($args);
?>

<link href="ncal_pliki/fullcalendar_002.css" rel="stylesheet">
<link href="ncal_pliki/fullcalendar.css" rel="stylesheet" media="print">
<script src="ncal_pliki/moment.js"></script>
<script src="ncal_pliki/jquery.js"></script>
<script src="ncal_pliki/fullcalendar.js"></script>
<script src="ncal_pliki/locale-all.js"></script>
<script>

function atsUpdateView(view){
	if (view.name == 'month'){
		userCalendarViewMonth();	
	} else if (view.name=='agendaWeek'){
		userCalendarViewWeek();
	} else {
		userCalendarViewDay();
	}
	
}

function atsAddEventShow(date,view){
	atsUpdateView(view);
	var d = date.toDate();
	//lert(date.format()+' Year:'+d.getFullYear()+' month:'+d.getMonth()+' day:'+d.getDate());
	//lert(date.hour());
	//lert(date.minute());
	//lert(d.getHours());
	if (date.minute()>0){
		addEventByDay(d.getFullYear(), d.getMonth()+1, d.getDate(),date.hour(),date.minute());
	} else {
		addEventByDay(d.getFullYear(), d.getMonth()+1, d.getDate(),date.hour());
	}
}


function atsGetOutlookHref(calEvent){
    var ll = encodeURI(calEvent.length);
    var eId = calEvent.id;
    if (calEvent.allDay == '1'){
    	ll='d';
    }	
    
    var et = calEvent.type;
    return '<a href="<?php echo E::routeHref('calendar/eventToOutook');?>?i='+eId+'&date='+calEvent.year+calEvent.month+calEvent.day+'&startTime='+calEvent.hour+calEvent.minute+'00'+'&t='+et+'&l='+ll+'&subject='+encodeURI(calEvent.subject)+'&desc='+encodeURI(calEvent.description)+'"><img src="assets/svg/outlook.svg" class="clIcon" height="16px" width="16px" alt="" style="border: none;" title="Pobierz do Outlook"></a>';
	
}


var imageAlert = 'images/bell.gif';

function atsRenderIcons(calEvent){
    var iconSet   = '<span nowrap="nowrap"><nobr>';
    var typeImage = getImageByType(calEvent.type);
    var string = '';


    if (typeImage != '')
    {
        // FIXME: Show actual type of event instead of "Type of Event" in title.
        iconSet += '<img class="clIcon" src="' + typeImage + '" title="'+calEvent.typeDesc+'" /> ';
    }

    iconSet += calEvent.iconsHTMLSmall;

    if (calEvent.reminderEnabled == 1)
    {
        iconSet += '<img class="clIcon" src="' + imageAlert + '" title="Ustawione przypomnienie" /> ';
    }

    if (calEvent.public == 1)
    {
        iconSet += '<img class="clIcon" src="images/public.gif" title="Wpis jawny" /> ';
    }
    
    iconSet += atsGetOutlookHref(calEvent);    

    iconSet += '</nobr></nowrap>';
    return iconSet;
}

function findOldCalEntryByEvent(calEvent){
	/*var ve = visibleEntries;
	var resEntry = null;
	ve.forEach(function(entry) {
	    //onsole.log(entry);
	    //;
	    //lert('entry.id'+entry.id);
	    if (entry.getData('eventID') == calEvent.id){
		    resEntry = entry;
		}
	});
	return resEntry;*/
    /* Make new entry. */
    //dayObject.entries = dayObject.entries.concat(new CalendarEntry());
    //entryIndex = dayObject.entries.length - 1;
    //entryObject = dayObject.entries[entryIndex];
    entryObject = new CalendarEntry();

    /* Fill in entry. */
    entryObject.year = calEvent.year;
    entryObject.month = calEvent.month;
    entryObject.day = calEvent.day;
    entryObject.hour = calEvent.hour;
    entryObject.minute = calEvent.minute;
    var valuesArray = [
        {
            0:'duration',
            1:calEvent.length
        },
        {
            0:'eventID',
            1:calEvent.id
        },
        {
            0:'dataItemID',
            1:calEvent.dataItemId
        },
        {
            0:'dataItemID',
            1:calEvent.dataItemId
        },
        {
            0:'eventType',
            1:calEvent.type
        },          
        {
            0:'dataItemType',
            1:calEvent.dataItemType
        },
        {
            0:'jobOrderID',
            1:calEvent.jobOrderId
        },        
        {
            0:'title',
            1:calEvent.subject
        },           
        {
            0:'description',
            1:calEvent.description
        },  
        {
            0:'reminderEnabled',
            1:calEvent.reminderEnabled
        },  
        {
            0:'reminderEmail',
            1:calEvent.reminderEmail
        },  
        {
            0:'reminderTime',
            1:calEvent.reminderTime
        }, 
        {
            0:'public',
            1:calEvent.public
        },  
        {
            0:'date',
            1:calEvent.date
        },
        {
            0:'dateUI',
            1:calEvent.date
        },                            
        {
            0:'time',
            1:calEvent.time
        },        
        {
            0:'timeUI',
            1:calEvent.time
        },
        {
            0:'userID',
            1:calEvent.userId
        },                    
        {
            0:'eventTypeDescription',
            1:calEvent.typeDesc
        },         
        {
            0:'enteredByFirstName',
            1:calEvent.userFirstName
        },        
        {
            0:'enteredByLastName',
            1:calEvent.userLastName
        },               
        {
            0:'allDay',
            1:calEvent.allDay
        }               
        ];
    entryObject.setDataByArray(valuesArray);
    entryObject.description = entryObject.getData('description');
    return entryObject;
	
}

var currentUserId = <?php echo $userId;?>;
var currentEventElement = null;
var currentCalEvent = null;

function atsSelectEventElement(evElement){
	if (currentEventElement!=null) $(currentEventElement).css('background-color',currentEventElementOldColor);
	if (evElement!=null){
		//$(evElement).css('border-color', 'red');
	    currentEventElementOldColor = $(evElement).css('background-color');
	    $(evElement).css('background-color', '#88D4FF');
	}
    currentEventElement = evElement;
    //lert('atsSelectEventElement'+currentEventElement);	
}

function atsEditEventShow(calEvent, view){
	atsUpdateView(view);
	var entry = findOldCalEntryByEvent(calEvent);
	handleClickEntry(entry);
}

var currentDayElementOldColor = null;
var currentDayElement = null;

var currentEventElementOldColor = null;


var atsFullCalendar = null;


	$(document).ready(function() {
		var initialLocaleCode = 'pl';

		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay,listMonth'
			},
			defaultDate: '<?php echo $defaultDate;?>',
			defaultView: '<?php echo $defaultView;?>',
			locale: initialLocaleCode,
			buttonIcons: false, // show the prev/next text
			weekNumbers: true,
			businessHours: {
			    // days of week. an array of zero-based day of week integers (0=Sunday)
			    dow: [ 0, 1, 2, 3, 4, 5, 6], // Monday - Thursday

			    start: '08:00', // a start time (10am in this example)
			    end: '18:00', // an end time (6pm in this example)
			},	
			//minTime:"07:00:00",	
			//maxTime:"20:00:00",
			scrollTime:"07:00:00",
			navLinks: false, // can click day/week names to navigate views
		    navLinkDayClick: function(date, jsEvent) {
			    //lert('navLinkDayClick', date.format());
		        console.log('day', date.format()); // date is a moment
		        console.log('coords', jsEvent.pageX, jsEvent.pageY);
		    },
		    navLinkWeekClick: function(weekStart, jsEvent) {
		        console.log('week start', weekStart.format()); // weekStart is a moment
		        //lert(weekStart.format());
		        console.log('coords', jsEvent.pageX, jsEvent.pageY);
		    },	
		    dayClick: function(date, jsEvent, view) {
		    	var isAgenda = (view.name == 'agendaWeek' || view.name == 'agendaDay');

		        //lert('Clicked on: ' + date.format());

		        //lert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
				
		        //lert('Current view: ' + view.name);
		       //
		        $('#calendar').fullCalendar( 'gotoDate', date );
		        $('#calendar').fullCalendar( 'select', date);
		        currentDayElement = this;
		        
		        /*var trg = this;
		        if (isAgenda){
					trg = jsEvent.target;
					var p = $(trg).position();
					alert('positionTop:'+p.top);
		        } 
		        
				if (currentDayElement!=null) $(currentDayElement).css('background-color',currentDayElementOldColor);
		        currentDayElementOldColor = $(trg).css('background-color');
		        $(trg).css('background-color', '#88D4FF');
		        currentDayElement = trg;*/		        
		        
		        atsAddEventShow(date, view);

		    },		    	    
			editable: false,
			eventLimit: true, // allow "more" link when too many events
			events: '<?php E::routeHrefO('calendar/eventSource');?>',
		    eventRender: function(calEvent, element, view) {
				var e = calEvent;
				//month/agendaWeek/agendaDay/listMonth
				var isLM = (view.name == 'listMonth');
				var isWeek = (view.name == 'agendaWeek');
				var isMonth = (view.name == 'month'); 
		    	/*element.qtip({
		            content: event.description
		        });*/
		        //event.title='dupa';
		        //element.onclick = function() {
		        //	alert('dupa');
		        //};
		        //element.html("dupa");
		        //lert('Current view: ' + view.name);
		        //lert('isLM:'+isLM);
		       
		        var iconsHtml = atsRenderIcons(calEvent);
		        //lert('e.startTime:'+e.startTime+' html:'+iconsHtml);
		        var evHdr = (isMonth || isWeek)?e.startTime:e.startTime+' - '+e.endTime;
		        if (!isLM){
			        var html = '<div class="clContent">';
			        html += '<div class="clTime" style="float:left;"><span style="vertical-align: top;font-weight: 700;font-size: 1em;">'+evHdr+'</span>'+iconsHtml+'</div>'; 
			        html += '<div class="clSubject">&nbsp;'+e.subject+'</div>';		        
			        html += '</div>';
			        element.html(html);
		        } else {
			        //lert(element.html());
			         var html = '<td class="fc-list-item-time fc-widget-content">'+e.startTime+' - '+e.endTime+'</td><td class="fc-list-item-marker fc-widget-content"><span style="float: right;">'+iconsHtml+'&nbsp;<span class="fc-event-dot"></span></span></td>';
			         html += '<td class="fc-list-item-title fc-widget-content"><a>&nbsp;'+e.subject+'</a></td>';
			         element.html(html);
			    }
		        return element;
		    },
		    eventClick: function(calEvent, jsEvent, view) {

		        //lert('EventId: ' + calEvent.id);
		        //lert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
		        //lert('View: ' + view.name);
				$('#calendar').fullCalendar( 'select', null);
				
		        $('#calendar').fullCalendar( 'gotoDate', calEvent.start );
		        //lert ('eonclick:'+currentEventElement);
		        if (currentCalEvent!=null && currentCalEvent.id == calEvent.id){
		        	var cb = document.getElementById('calendarEditEventBtn');
		        	//lert (cb);
		        	cb.click();
		        	//calendarEditEvent(currentViewedEntry);
		        	atsSelectEventElement(this);
		        	currentCalEvent = null;
			    } else {
		        	atsSelectEventElement(this);
		        	currentCalEvent = calEvent;
		        	atsEditEventShow(calEvent, view);
			    }
		        
		        		        

		    },
		    eventMouseover: function( calEvent, jsEvent, view ) { 
		    	 //lert('Event: ' + calEvent.title);
		    	 $(this).css('border-color', 'yellow');
		    },
		    eventMouseout:function( calEvent, jsEvent, view ) { 
		    	 //lert('Event: ' + calEvent.title);
		    	 $(this).css('border-color', 'green');
		    },
		    unselectAuto: false,
		    select:function( start, end, jsEvent, view ){
			    //lert(jsEvent.target);
		    	//$(jsEvent.target).css('background-color', 'red');
		    	 $(".fc-highlight").css("background", "red");
		    	 atsSelectEventElement(null);
		    	 //$(".fc-highlight").css("border-color", "green");
			},
		    
			

			
		});

		// build the locale selector's options
		$.each($.fullCalendar.locales, function(localeCode) {
			$('#locale-selector').append(
				$('<option/>')
					.attr('value', localeCode)
					.prop('selected', localeCode == initialLocaleCode)
					.text(localeCode)
			);
		});

		// when the selected option changes, dynamically change the calendar option
		$('#locale-selector').on('change', function() {
			if (this.value) {
				$('#calendar').fullCalendar('option', 'locale', this.value);
			}
		});
	});

</script>
<style>

	body {
		margin: 0;
		padding: 0;
		font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
		font-size: 14px;
	}

	#top {
		background: #eee;
		border-bottom: 1px solid #ddd;
		padding: 0 10px;
		line-height: 40px;
		font-size: 12px;
	}

	#calendar {
		max-width: 900px;
		margin: 0px auto;
		padding: 0 10px;
	}

</style>

	<div id="top" style="display:none;">

		Locales:
		<select id="locale-selector"><option value="en" selected="selected">en</option><option value="af">af</option><option value="ar">ar</option><option value="ar-dz">ar-dz</option><option value="ar-kw">ar-kw</option><option value="ar-ly">ar-ly</option><option value="ar-ma">ar-ma</option><option value="ar-sa">ar-sa</option><option value="ar-tn">ar-tn</option><option value="bg">bg</option><option value="ca">ca</option><option value="cs">cs</option><option value="da">da</option><option value="de">de</option><option value="de-at">de-at</option><option value="de-ch">de-ch</option><option value="el">el</option><option value="en-au">en-au</option><option value="en-ca">en-ca</option><option value="en-gb">en-gb</option><option value="en-ie">en-ie</option><option value="en-nz">en-nz</option><option value="es">es</option><option value="es-do">es-do</option><option value="et">et</option><option value="eu">eu</option><option value="fa">fa</option><option value="fi">fi</option><option value="fr">fr</option><option value="fr-ca">fr-ca</option><option value="fr-ch">fr-ch</option><option value="gl">gl</option><option value="he">he</option><option value="hi">hi</option><option value="hr">hr</option><option value="hu">hu</option><option value="id">id</option><option value="is">is</option><option value="it">it</option><option value="ja">ja</option><option value="kk">kk</option><option value="ko">ko</option><option value="lb">lb</option><option value="lt">lt</option><option value="lv">lv</option><option value="mk">mk</option><option value="ms">ms</option><option value="ms-my">ms-my</option><option value="nb">nb</option><option value="nl">nl</option><option value="nl-be">nl-be</option><option value="nn">nn</option><option value="pl">pl</option><option value="pt">pt</option><option value="pt-br">pt-br</option><option value="ro">ro</option><option value="ru">ru</option><option value="sk">sk</option><option value="sl">sl</option><option value="sr">sr</option><option value="sr-cyrl">sr-cyrl</option><option value="sv">sv</option><option value="th">th</option><option value="tr">tr</option><option value="uk">uk</option><option value="vi">vi</option><option value="zh-cn">zh-cn</option><option value="zh-tw">zh-tw</option></select>

	</div>

	<div id='calendar'></div>