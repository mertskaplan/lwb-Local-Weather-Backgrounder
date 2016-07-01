<?php

/*
	Name:		lwb - Local Weather Backgrounder
	Desc:		IP based Local Weather Live Wallpapers for Developers
	Version:	1.0
	Author:		Mert S. Kaplan, mail@mertskaplan.com
	License:	GPLv3, GNU General Public License - version 3, https://github.com/mertskaplan/lwb-Local-Weather-Backgrounder/blob/master/LICENSE
	Web:		http://lab.mertskaplan.com/lwb
*/

	function find($first, $latest, $text) {
		@preg_match_all('/' . preg_quote($first, '/') .
		'(.*?)'. preg_quote($latest, '/').'/i', $text, $m);
		return @$m[1];
	}
	
	function getUserIP() {
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if		(filter_var($client, FILTER_VALIDATE_IP))	{$ip = $client;}
		elseif	(filter_var($forward, FILTER_VALIDATE_IP))	{$ip = $forward;}
		else    {$ip = $remote;}
		return $ip;
	}
	
	function randomPic($dir, $cw) {
		$files = glob($dir . $cw . '/*.*');
		$file = array_rand($files);
		return $files[$file];
	}

	$ip = getUserIP();
	$content		= file_get_contents("http://ip-api.com/php/$ip");
	$city			= find('city";s:6:"', '";', $content);
	$countryCode	= find('countryCode";s:2:"', '";', $content);

	  $city			= strtolower(str_replace(' ','-',$city[0]));
	  $countryCode	= strtolower($countryCode[0]);
	  
	include ("countries.php");
	
	$capital = $country["$countryCode"][1];
	$country = $country["$countryCode"][0];
	
		$capital = strtolower(str_replace(' ','-',$capital));
		$country = strtolower(str_replace(' ','-',$country));
		
	@$content	= file_get_contents("https://www.timeanddate.com/worldclock/$country/$city");
	$control	= find('<span id=ct class=h1>',			'</span>',	$content);

	if	(empty($control[0])) {
		@$content = file_get_contents("https://www.timeanddate.com/worldclock/$country/$capital");
	}
	
	$weather 	= find('<img class=mtt title="',		'.',		$content);	
	$localTime	= find('<span id=ct class=h1>',			'</span>',	$content);
	$sunrise	= find('<div id=tl-sr-i class=tl-dt>',	'<br>',		$content);	
	$sunset		= find('<div id=tl-ss-i class=tl-dt>',	'<br>',		$content);
	
		$weather	= strtolower(str_replace(' ','-',$weather[0]));
		$localTime	= $localTime[0];
		$sunrise	= $sunrise[0]	. ":00";
		$sunset		= $sunset[0]	. ":00";

	$dayLength			= strtotime($sunset) - strtotime($sunrise);
	$sunLength			= ($dayLength * 28*60) / (12*60*60);		// 28 minutes / 12 hours
	$skylineNegative	= ($sunLength *  8*60) / (28*60);			//  8 minutes / 28 minutes
	$skylinePositive	= ($sunLength * 20*60) / (28*60);			// 20 minutes / 28 minutes
	$sunriseStart		= strtotime($sunrise) - $skylineNegative;
		$sunriseStart	= date("H:i:s",$sunriseStart);
	$sunriseEnd			= strtotime($sunrise) + $skylinePositive;
		$sunriseEnd		= date("H:i:s",$sunriseEnd);
	$sunsetStart		= strtotime($sunset) - $skylinePositive;
		$sunsetStart	= date("H:i:s",$sunsetStart);
	$sunsetEnd			= strtotime($sunset) + $skylineNegative;
		$sunsetEnd		= date("H:i:s",$sunsetEnd);

	
	if		($localTime >= $sunriseStart && $localTime <= $sunriseEnd)	{$cycle = "sunrise";}
	elseif	($localTime >= $sunsetStart && $localTime <= $sunsetEnd)	{$cycle = "sunset";}
	elseif	($localTime > $sunsetEnd || $localTime < $sunriseStart)		{$cycle = "nighttime";}
	elseif	($sunsetStart == "00:00:00" || $sunsetEnd == "00:00:00" || $sunriseStart == "00:00:00" || $sunriseEnd == "00:00:00") {$cycle = "daytime";}
	else	{$cycle = "daytime";}
	

	if		($weather == "clear" || $weather == "sunny" || $weather == "partly-sunny" || $weather == "cool" || $weather == "extremely-hot" || $weather == "quite-cool" || $weather == "warm") {
		if		($cycle == "sunrise")	{$cw = "clear/sunrise";}
		elseif	($cycle == "sunset")	{$cw = "clear/sunset";}
		elseif	($cycle == "nighttime")	{$cw = "clear/nighttime";}
		else	{$cw = "clear/daytime";}
	}
	elseif	($weather == "cloudy" || $weather == "passing-clouds" || $weather == "broken-clouds" || $weather == "more-clouds-than-sun" || $weather == "mostly-cloudy" || $weather == "partly-cloudy" || $weather == "low-clouds" || $weather == "overcast" || $weather == "scattered-clouds" || $weather == "mild") {
		if		($cycle == "sunrise")	{$cw = "cloudy/sunrise";}
		elseif	($cycle == "sunset")	{$cw = "cloudy/sunset";}
		elseif	($cycle == "nighttime")	{$cw = "cloudy/nighttime";}
		else	{$cw = "cloudy/daytime";}
	}
	elseif	($weather == "rain" || $weather == "light-rain" || $weather == "heavy-rain" || $weather == "rain-showers" || $weather == "drizzle" || $weather == "thunderstorms" || $weather == "sprinkles" || $weather == "scattered-showers" || $weather == "thundershowers" || $weather == "light-mixture-of-precip") {
		if		($cycle == "sunrise")	{$cw = "rain/sunrise";}
		elseif	($cycle == "sunset")	{$cw = "rain/sunset";}
		elseif	($cycle == "nighttime")	{$cw = "rain/nighttime";}
		else	{$cw = "rain/daytime";}
	}
	elseif	($weather == "light-snow" || $weather == "snow-showers" || $weather == "snow-flurries" || $weather == "chilly") {
		if		($cycle == "sunrise")	{$cw = "snow/sunrise";}
		elseif	($cycle == "sunset")	{$cw = "snow/sunset";}
		elseif	($cycle == "nighttime")	{$cw = "snow/nighttime";}
		else	{$cw = "snow/daytime";}
	}
	elseif	($weather == "fog" || $weather == "ice-fog" || $weather == "dense-fog" || $weather == "haze") {
		if		($cycle == "sunrise")	{$cw = "fog/sunrise";}
		elseif	($cycle == "sunset")	{$cw = "fog/sunset";}
		elseif	($cycle == "nighttime")	{$cw = "fog/nighttime";}
		else	{$cw = "fog/daytime";}
	}
	else	{
		if		($cycle == "sunrise")	{$cw = "clear/sunrise";}
		elseif	($cycle == "sunset")	{$cw = "clear/sunset";}
		elseif	($cycle == "nighttime")	{$cw = "clear/nighttime";}
		else	{$cw = "clear/daytime";}
	}
	
	$file = randomPic("img/", $cw);
	$img = randomPic("img/", $cw);
	
	$what = getimagesize($file);
	switch(strtolower($what['mime'])) {
		case 'image/png':
			$form = "image/png";
			$img = imagecreatefrompng($file);
			break;
		case 'image/jpeg':
			$form = "image/jpeg";
			$img = imagecreatefromjpeg($file);
			break;
		case 'image/gif':
			$form = "image/gif";
			$img = imagecreatefromgif($file);
			break;
		default: die();
	}

	$new = imagecreatetruecolor($what[0],$what[1]);
	imagecopy($new,$img,0,0,0,0,$what[0],$what[1]);

	header('Content-Type: $form');
	imagejpeg($new);
	imagedestroy($new);