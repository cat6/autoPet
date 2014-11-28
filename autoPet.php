<?php
$animalGroup = "type_OO";	// OO - other, DOG, CAT
$animalType = "Rodent";  // Rodent, Cat
$gender = "";	// ",gender_f" - female, ",gender_m" for male
$date = date("l jS \of F Y h:i A");

// ideally cron should look like this: /usr/bin/php /home/cPusername/public_html/path/to/cron.php
// /usr/bin/php /home/bikewdts/public_html/cron/autoPet.php

$animalSearch = $animalGroup . $gender;


$rowsToRequest = 10;
$page = 1;
$url = "http://www.petharbor.com/results.asp?searchtype=ADOPT&friends=1&samaritans=1&nosuccess=0&rows=" . $rowsToRequest ."&imght=120&zip=80443&amp;miles=10&shelterlist=%27TRNT1%27,%27TRNT%27,%27TRNT2%27,%27TRNT3%27,%27TRNT4%27,%27TRNT5%27&atype=&where=" . $animalSearch ."&PAGE=" . $page;

function checkCriteria($row)
{
	// Returns 1 if a record ($row) meets specified criteria for potential use as an advanced search function.
	return 1;
}

function paginate($url, $page)
{
	// returns $url, but with the last digit changed to $page
	$url = substr($url, 0, -1).$page;
	//print "Paginage, page: " . $page . "<br/>";
	//print "URL: " . $url . "<br/>";
	return $url;
}

function get_string_between($string, $start, $end)
{
	// From: http://stackoverflow.com/questions/5696412/get-substring-between-two-strings-php
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}


function fixImage($pic)
{
	// returns a properly formatted <img> tag given the line a pic is on

	// get all text from 'src"' to next '"'
	$pic = get_string_between($pic, 'src="', '"');

	$pic = str_replace("thumb", "Detail", $pic); // find and replace "thumb" with "detail"
	$pic = "http://www.petharbor.com/" . $pic;
	$pic = "<img src='" . $pic . "' alt='Pet Candidate Image' width='150' height='100'>\n";

	return $pic;
}

function curl($url) {
    $ch = curl_init();  // Initialising cURL
    curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
    $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
    curl_close($ch);    // Closing cURL
    return $data;   // Returning the data from the function
}

function traverseRow($line)
{
	// Returns an array of animal data based upon $row

	//print "LINE: " . $line . "<br/><br/>";
	$i = 0;

	foreach($line->find('td') as $element)
	{
		if($i == 0)
		{
			print fixImage($element) . "<br/>\n";
		}		
		if($i == 1)
		{
			print "Type: " . $element . "<br/>\n";
		}
		if($i == 2)
		{
			print "Name: " . $element . "<br/>\n";
		}
		if($i == 3)
		{
			print "Gender: " . $element . "<br/>\n";
		}
		if($i == 4)
		{
			print "Colour: " . $element . "<br/>\n";
		}
		if($i == 5)
		{
			print "Species: " . $element . "<br/>\n";
		}
		if($i == 6)
		{
			print "Age: " . $element . "<br/>\n";
		}
		if($i == 7)
		{
			print "Location: " . $element . "<br/>\n\n";
		}
		$i++;
	}
}

// Include the library
include('simple_html_dom.php');

// Create DOM from URL or file
//$html = file_get_html($url);
//$url = paginate($url, $page++);
$html = file_get_html($url);

//print "url: " . $url . "<br/>";

print "<!DOCTYPE html>\n<html>\n<head>\n<title>AutoPet: Animal Search Results " . $date . "</title>\n<meta charset='UTF-8'>\n</head>\n<body>\n";
print "<div><h1>AutoPet Animal Search results for: " . $date . "</h1></div>";
$pos = (int)strpos($html->innertext, "Sorry!");
//print "pos: " . (int)$pos . "<br/>";
$j = 0;
while($pos <= 0)
{
	$i = 0;
	print "<p>\n";
	// Find all images 
	//print "<table>\n";
	$rows = $html->find('tr');
	foreach($rows as $row)
	{
		$pos = strpos($row, $animalType); // Should always be dependable, since it's not permitted to search for cats/dogs/other at the same time.
		if($pos != false)
		{
			//print "Animal $i:<br/>";
			if(checkCriteria($row) == 1)
			{
				traverseRow($row, $i);
				$i++;
			}
		}
	}
	//print "</table>\n";
	print "</p>\n";
	$page++;
	$url = paginate($url, $page);
	$html = file_get_html($url);
	$pos = (int)strpos($html->innertext, "Sorry!");
	$j += $i;
	//print "pos:" . (int)$pos . "<br/>";
	//print "html: " . $html->innertext . "<br/>";
 }
 print "No. of Records: " . $j . "<br/>";
 print "</body></html>";

?>