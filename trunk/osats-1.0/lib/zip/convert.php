<?php
// *********************
// * Conversion helper *
// * Created by: Seven *
// *********************

// Since ZIP-files like using Dostime, i've written this small helper function
// set, making it easier to read and write Dostime

// clear documentation about Dostime:
// www.vsft.com/hal/dostime.htm
// thanks Vilma Software! :)


// format according to MSDN:
// 5 bytes for seconds divided by 2, 6 bytes for minutes and 5 bytes for the hours :)

// write 16bit dostime output

function dostime_get($offset) {

	// take about 2 kilograms of hours
	$doshour = gmdate("G") + 1;
	$doshour = $doshour + $offset;

		// we won't be worrying about the date, that'll be the next function ;)
	if($doshour > 23) $doshour = $doshour - 24;
	if($doshour < 0) $doshour = $doshour + 24;

	$doshour = $doshour * pow(2,11);

	// mix it with about 250 grams of minutes
	$dosminute = gmdate("i") * pow(2,5);

	// then add a pinch of seconds
	$dossecond = round(gmdate("s") / 2);

	// mix them together and whack them in the oven for about 20 minutes
	$dostime = $doshour+$dosminute+$dossecond;

	// and it's ready to be served! :)
	return $dostime;

	// "whack" is a registered trademark of Jamie Oliver. All rights reserved.
};

// format according to MSDN:
// 5 bytes for days, 4 for month, 7 for years from 1980... can go for 128 years then, so warn me in 2108 ;)

function dosdate_get($offset) {
	// date
	$dosyear = (gmdate("Y") - 1980);
	$dosmonth = gmdate("m");
	$dosday = gmdate("j");

	// checking if date is valid
	// starting with... is the day too high after applying offset?
	if(gmdate("G") + $offset > 23) {
		$dosday++;
		if($dosday > date("t")) {
			$dosday = 1;
			$dosmonth++;
			if($dosmonth > 12) {
				$dosmonth = 1;
				$dosyear++;
			};
		};
	};

	// then, is the day too low after applying offset?
	if(gmdate("G") + 1 + $offset < 0) { // +1 to fix the erm... standard -1 offset this pc has... strange thou :p
		$dosday = $dosday - 1;
		if($dosday < 1) {
			// ok, little helper array, containing the months that have 30 days:)
			$dirtydays = array(4,6,9,11);
			if(in_array($dirtydays,$dosmonth - 1)) { 	// is it one month after one of the feared months, added in the array above?
				$dosday = 30;
			} elseif ($dosmonth == 3) { 			// is it march then?
				$dosday = 28+date("L");
			} else {					// then the month before this one must have 31 days :)
				$dosday = 31;
			};

			$dosmonth --;
			if($dosmonth < 1){
				$dosmonth == 12;
				$dosyear --; // i aint checking this one, we're not creating files b4 1980 anyway ;)
			};
		};
	};

	// wow, that took me some thinking, let's go to an easier part, returning!
	$dosyear = $dosyear * pow(2,9);
	$dosmonth = $dosmonth * pow(2,5);
	return $dosyear+$dosmonth+$dosday;
}

// Now this process must be reversed aswell. I think the most easy method for this is just returning an array with data.

function dostime_return($dostime) {
	$dostime = decbin(ascii2dec($dostime)); //looks nasty, but hey, it works ;)
	$dostime = str_pad($dostime,16,"0",STR_PAD_LEFT);

	// retreiving the needed data... 5-6-5 was the format
	// *** Warning! *** Waarschuwing! *** Achtung! ***
	// I don't know if this works on little endian machines the way it works on big-endian ones
	// So let's hope for the best

	$return['hours'] = substr($dostime,0,5);
	$return['minutes'] = substr($dostime,5,6);
	$return['seconds'] = substr($dostime,11,5);

	unset($dostime);

	// now processing the info to the right format
	$return['hours'] = bindec($return['hours']);
	$return['minutes'] = bindec($return['minutes']);
	$return['seconds'] = bindec($return['seconds']) * 2;
	return $return;
}

// this is mostly a copy of dostime_return
function dosdate_return($dosdate) {
	$dosdate = decbin(ascii2dec($dosdate)); //looks nasty, but hey, it works ;)
	$dosdate = str_pad($dosdate,16,"0",STR_PAD_LEFT);

	// retreiving the needed data... 5-4-7 was the format
	// *** Warning! *** Waarschuwing! *** Achtung! ***
	// I don't know if this works on little endian machines the way it works on big-endian ones
	// So let's hope for the best

	$return['year'] = substr($dosdate,0,7);
	$return['month'] = substr($dosdate,7,4);
	$return['day'] = substr($dosdate,11,5);

	unset($dosdate);

	// now processing the info to the right format
	$return['day'] = bindec($return['day']);
	$return['month'] = bindec($return['month']);
	$return['year'] = bindec($return['year']) + 1980;
	return $return;
}

// Also useful is this ascii2dec convertor, will be a well used conversion when reading a zipfile
// simple but powerful :)

function ascii2dec($input) {
    $output = '';
	$end = strlen($input);
	$multiplier = 1;
	for($i=0; $i < $end; $i++) {
		$output = $output + (ord($input[$i]) * $multiplier); // I think Max wants some credit for this [$i] method
		$multiplier = $multiplier * 256;
	}
	unset ($input);
	return $output;
}

// Extension to content-type header conversion.
function ext2cth($filename) {
	$filename = explode(".",$filename);
	$extension = array_pop($filename);

	// I kinda need a gigantic array for this, i'll do this for now by including this array and setting a little var so I know it's
	// been included

	if(!isset($types_is_included)){
		require ("./lib/zip/array_filetypes.php");
		$types_is_included = TRUE;
	}

	$extension = strtolower($extension);
	$filetype = @$type[$extension];
	if(empty($filetype)) {
		$filetype = $type['default'];
	}
	return $filetype;
}
?>
