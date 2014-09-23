<?php
/**
 * This file is part of LeonardoCA\Tools for Nette Framework
 * Copyright (c) 2012 Leonard Odložilík
 * For the full copyright and license information,
 * please view the file license.txt that was distributed with this source code.
 */
use LeonardoCA\Tools\ProcessMonitor;
use Tracy\Debugger;

/**
 * LeonardoCA\Tools\ProcessMonitor::dump shortcut.
 *
 * @param string $msg  message
 * @param mixed  $data - var, object to be dumped
 * @return void
 */
function pm($msg, $data = null)
{
	ProcessMonitor::dump($msg, $data);
}

/**
 * LeonardoCA\Tools\ProcessMonitor::saves time interval shortcut.
 *
 * @param string $msg   message
 * @param mixed  $data  - var, object to be dumped
 * @param string $timer name
 * @return void
 */
function pms($msg, $data = null, $timer = null)
{
	ProcessMonitor::addSummary($msg, $data, $timer);
}

/**
 * LeonardoCA\Tools\ProcessMonitor::saves time interval shortcut.
 *
 * @param string $msg  message
 * @param mixed  $data - var, object to be dumped
 * @return void
 */
function pme($msg, $data = null)
{
	$backupMaxLength = Debugger::$maxLen;
	Debugger::$maxLen = 500;
	$backupMode = ProcessMonitor::$reportMode;
	ProcessMonitor::$reportMode = ProcessMonitor::SHOW_DETAIL;
	ProcessMonitor::addSummary($msg, $data);
	ProcessMonitor::$reportMode = $backupMode;
	Debugger::$maxLen = $backupMaxLength;
}

/**
 * LeonardoCA\Tools\ProcessMonitor detail dump
 *
 * @param string $msg
 * @param null   $data
 * @param null   $dataDescription
 * @param int    $maxDepth
 * @return void
 */
function pmd(
	$msg = 'Detail Info', $data = null, $dataDescription = null, $maxDepth = 2
) {
	$backupDepth = Debugger::$maxDepth;
	Debugger::$maxDepth = $maxDepth;
	ProcessMonitor::dump(
		"<em style='color:#339'>$msg</em>",
		$data,
		null,
		ProcessMonitor::SHOW_DETAIL,
		$dataDescription
	);
	Debugger::$maxDepth = $backupDepth;
}

/**
 * LeonardoCA\Tools\ProcessMonitor::reset cleans internal summary and timers
 *
 * @param string $msg
 * @return void
 */
function pmr($msg = 'PM reset')
{
	ProcessMonitor::reset($msg);
}
