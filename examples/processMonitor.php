<?php

/**
 * This file is part of LeonardoCA\Tools for Nette Framework
 * Copyright (c) 2012 Leonard Odložilík
 * For the full copyright and license information,
 * please view the file license.txt that was distributed with this source code.
 */

use LeonardoCA\Tools\ProcessMonitor;
use Tracy\Debugger;

date_default_timezone_set('Europe/Prague');

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Tools/shortcuts/processMonitor.php';

// Demonstration of ProcessMonitor
// this is fake demo how some import process might be monitored

// some messy code for the cool stuff
echo "<!DOCTYPE html>
	<html lang='en'>
	<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<title>Fake ProcessMonitor Demo</title>
	<link rel='stylesheet' href='css/bootstrap.min.css'>
	<script src='js/jquery-1.8.2.min.js'></script>
	</head>
	<body style='padding: 10px; padding-bottom:60px; margin-right:150px;'>
	<div id='staticPanel' style='position:fixed; top: 10px; right: 5px; padding:10px; text-align: right;'>
			<a id='expandAll' href='#' data-pm-expanded='0'>Expand All</a><br/><br/>
			<a id='pmAutoScroll' href='#'>Autoscroll ON</a><br/><br>
			<a id='pmOnUp' href='#' data-pm-expanded='0'>Top</a><br/><br>
			<a id='pmOnDown' href='#' data-pm-expanded='0'>Bottom</a><br/><br>
	</div>
	</body></html>";
echo '
	<script type="text/javascript">
	/* <![CDATA[ */
	var autoscroll = 1;
	var step = 10;
	var currPos = 0;
	var speed = 500;

	var autoscrollLink = $("#pmAutoScroll");
	autoscrollLink.data("autoscroll", autoscroll);

	if (autoscroll == 1) {
		autoscrollLink.data("timer", window.setInterval(function() {
			window.scrollTo(0,document.body.scrollHeight);
		}, speed));
	}
	autoscrollLink.on(\'click\', function ($e) {
		if ($(this).data("autoscroll") == 1) {
			$(this).data("autoscroll", 0);
			$(this).text("Autoscroll OFF");
			window.clearInterval($("#pmAutoScroll").data("timer"));
		} else {
			$(this).data("autoscroll", 1);
			$(this).text("Autoscroll ON");
			$("#pmAutoScroll").data("timer", window.setInterval(function() {
				window.scrollTo(0,document.body.scrollHeight);
			}, speed));
		}
		event.preventDefault();
	});
	//$(document).ready(function(){
		$("#expandAll").on(\'click\', function ($e) {
			if ($(this).attr("data-pm-expanded") == 1) {
				$(this).text("Expand All");
				$(this).attr("data-pm-expanded", 0);
				$("a[rel=\'next\']").next().hide();
			} else {
				$(this).text("Collapse All");
				$(this).attr("data-pm-expanded", 1);
				$("a[rel=\'next\']").next().show();
			}
			event.preventDefault();
		});
		$("#pmOnDown").on(\'click\', function ($e) {
			window.scrollTo(0,document.body.scrollHeight);
			event.preventDefault();
		});
		$("#pmOnUp").on(\'click\', function ($e) {
			window.scrollTo(0,0);
			event.preventDefault();
		});
	//});
	/* ]]> */
	</script>';
flush();

function addJsAfterTheScriptIsDone()
{
	echo '
	<script type="text/javascript">
	/* <![CDATA[ */
	$("a[rel=\'next\']").on(\'click\', function ($e) {
		$(this).next().toggle();
		event.preventDefault();
	});
	/* ]]> */
	</script>';
	flush();
}

;

// following code is for fake demo

$filesList[] = ['name' => 'file1.xml', 'size' => '658', 'time' => 1.2];
$filesList[] = ['name' => 'file2.xml', 'size' => '1568', 'time' => 2.2];
$filesList[] = ['name' => 'file3.xml', 'size' => '325', 'time' => 0.7];
$filesList[] = ['name' => 'file4.xml', 'size' => '588', 'time' => 1];
$filesList[] = ['name' => 'file5.xml', 'size' => '1025', 'time' => 1.4];
$filesList[] = ['name' => 'file6.xml', 'size' => '958', 'time' => 1.3];

$memoryLeakData = [];

function consumeMemory($amount, &$memoryLeakData)
{
	for ($i = 0; $i < $amount; $i++) {
		$memoryLeakData[] = array_fill(0, 10, '0123456789');
	}
}

function runApiCallToGetXMLFile($fileInfo, &$memoryLeakData)
{
	usleep($fileInfo['time'] * 1000000);
	consumeMemory($fileInfo['size'], $memoryLeakData);
}

function parseXMLFile($fileInfo, &$memoryLeakData)
{
	usleep($fileInfo['time'] * 1000000 / 2);
	consumeMemory($fileInfo['size']*10, $memoryLeakData);
}

function storeData($fileInfo, &$memoryLeakData)
{
	// pm is shortcut for ProcessMonitor::dump()
	pm('some debug msg');
	usleep($fileInfo['time'] * 1000000 / 3);
	consumeMemory($fileInfo['size'] / 10, $memoryLeakData);
}

// end of fake demo specific code

// processMonitor extends Tracy\Debugger and mostly respects it's configuration
// therefore configure Debugger first
Debugger::detectDebugMode();
Debugger::enable();
Debugger::$maxDepth = 1;
// initialize ProcessMonitor
ProcessMonitor::$reportMode = ProcessMonitor::SHOW_DETAIL;
// ProcessMonitor::start is intended to be run only once
ProcessMonitor::start('some API import (this is only fake demo)');

$count = 0;

// this is how typical processing loop looks like
foreach ($filesList as $fileInfo) {
	// while debugging scripts which repeat some actions multiple times
	// reset process monitor timers in each loop
	pmr('some api call to import ' . $fileInfo['name']);

	// 1. step some api call
	runApiCallToGetXMLFile($fileInfo, $memoryLeakData);
	// pms is shortcut for ProcessMonitor::addSummary()
	// when processing can be split in several steps as in this case
	// it is very useful to track time and memory usage for each step separately
	// track api call time + download response time
	pms(
		"get file {$fileInfo['name']} xml: " . ProcessMonitor::formatSize(
			$fileInfo['size'] * 1000,
			ProcessMonitor::SIZE_AUTO
		)
		. " <a href='/examples/processMonitor.php?file={$fileInfo['name']}'>Run again &gt;&gt; </a>"
		. " <br/> see imported xml: <a href='/some_url/{$fileInfo['name']}' target='_blank'>"
		. $fileInfo['name'] . "</a>",
		null,
		'time_api_call'
	);

	// 2. step - parsing xml response
	parseXMLFile($fileInfo, $memoryLeakData);
	// track time to validate and parse xml
	pms('parsed ' . $fileInfo['name'], $fileInfo, 'time_parse');

	// 3. step - preparing and storing data to db, etc
	storeData($fileInfo, $memoryLeakData);
	// track time to process and store the data
	pms('processed ' . $fileInfo['name'], $fileInfo, 'time_processed');
	// intentionally condition for always true in this example
	if ($error = true) {
		// pme is shortcut to output error messages
		// you may include whatever data useful for debugging
		// for more variables to dump use array
		pme("Error description", ['some useful data']);
	}
	$count++;
}

pmr("XML files processed: $count");
pmr(
	'Total run time: ' . ProcessMonitor::formatTime(
		ProcessMonitor::getTotalTime()
	)
);

// this code should be called on application shutdown event in nette:
// $this->application->onShutdown[] = addJsAfterTheScriptIsDone();
addJsAfterTheScriptIsDone();
