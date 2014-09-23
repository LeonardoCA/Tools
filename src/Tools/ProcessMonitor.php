<?php
/**
 * This file is part of LeonardoCA\Tools for Nette Framework
 * Copyright (c) 2012 Leonard Odložilík
 * For the full copyright and license information,
 * please view the file license.txt that was distributed with this source code.
 */
namespace LeonardoCA\Tools;

use Nette\StaticClassException;
use Tracy\Debugger;

/**
 * ProcessMonitor - provides time and memory monitoring
 *
 * @author Leonard Odložilík
 * @todo   Report Levels
 * @todo   Console output
 */
class ProcessMonitor
{

	/** Mode */
	const
		SHOW_NONE = -99,
		SHOW_ERRORS = -1,
		SHOW_NORMAL = 0,
		SHOW_DETAIL = 1;

	/** Size */
	const
		SIZE_AUTO = 'auto',
		SIZE_MB = 1048576,
		SIZE_KB = 1024,
		SIZE_B = 1;

	/** @var array */
	private static $units = array(
		self::SIZE_MB => 'MB',
		self::SIZE_KB => 'KB',
		self::SIZE_B => 'B'
	);

	/** @var array */
	private static $reportModes = array(
		self::SHOW_ERRORS => 'SHOW_ERRORS',
		self::SHOW_NONE => 'SHOW_NONE',
		self::SHOW_NORMAL => 'SHOW_NORMAL',
		self::SHOW_DETAIL => 'SHOW_DETAIL'
	);

	/** @var int */
	public static $reportMode = self::SHOW_NORMAL;

	/** @var bool */
	public static $useSummary = false;

	/** @var string */
	public static $bytesUnit = self::SIZE_AUTO;

	/** @var bool */
	private static $enabled = false;

	/** @var array */
	private static $summary = array();

	/** @var array */
	private static $timers = array();

	/** @var int */
	private static $startTime;

	/** @var int */
	private static $time;

	/** @var int */
	private static $lastIntervalTime = 0;

	/** @var string */
	private static $lineEnding = '<br>';

	/** @var bool */
	private static $consoleMode;



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}



	/**
	 * Must be called to enable process monitoring
	 *
	 * @param string $msg
	 * @return void
	 */
	public static function start($msg = 'process monitor starts...')
	{
		if (!Debugger::isEnabled()) {
			return;
		}
		self::$consoleMode = !(empty($_SERVER['HTTP_X_REQUESTED_WITH'])
			&& preg_match(
				'#^Content-Type: text/html#im',
				implode("\n", headers_list())
			));
		if (self::$consoleMode) {
			self::$lineEnding = "\n";
		}
		for ($i = 0; $i < ob_get_level(); $i++) {
			ob_end_flush();
		}
		self::$enabled = true;
		self::$startTime = microtime(true);
		self::$time = self::$startTime;
		self::addSummary(
			$msg . ' (reportMode: ' . self::$reportModes[self::$reportMode]
			. ')'
		);
	}



	/**
	 * Resets counter and summary
	 *
	 * @param bool|string Text message to output
	 * @return void
	 */
	public static function reset($msg = 'PM reset')
	{
		if (!self::$enabled) {
			return;
		}
		self::$time = microtime(true);
		self::$lastIntervalTime = 0;
		self::$summary = array();
		self::$timers = array();
		if (self::$reportMode > self::SHOW_ERRORS) {
			echo '<br>';
			if ($msg) {
				echo self::status() . ' <small>' . date(
						'[Y-m-d H:m:s]',
						time()
					) . '</small> ' . $msg . '<br>';
			}
			flush();
		}
	}



	/**
	 * Dump
	 *
	 * @param string $msg
	 * @param mixed  $data
	 * @param string $timer
	 * @param int    $reportMode
	 * @param mixed  $description
	 * @return string
	 */
	public static function dump(
		$msg,
		$data = null,
		$timer = null,
		$reportMode = self::SHOW_NORMAL,
		$description = null
	) {
		if (!self::$enabled
			|| (Debugger::$productionMode
				&& ($reportMode == self::SHOW_DETAIL))
		) {
			return false;
		}
		$log = self::status($timer) . $msg;
		if (self::$reportMode && $data) {
			$log .= ' <a href="#" rel="next">' . $description
				. ' <abbr>►</abbr></a> '
				. "<div class='hid' style='display:none'>";
			if (class_exists('\DibiFluent') && ($data instanceof \DibiFluent)) {
				ob_start();
				$data->test();
				$log .= ob_get_clean();
			} else {
				$log .= Debugger::dump($data, true);
			}
			$log .= "</div>";
		}
		$log .= '<br>';
		if (self::$reportMode > self::SHOW_ERRORS) {
			echo $log;
			flush();
		}
		return $log;
	}



	/**
	 * Add summary
	 *
	 * @param string $msg  Message
	 * @param mixed  $data Any variable which will be dumped
	 * @param string $timerName
	 * @return void
	 */
	public static function addSummary($msg, $data = null, $timerName = null)
	{
		if (!self::$enabled) {
			return;
		}
		if (self::$reportMode > self::SHOW_ERRORS) {
			echo '<b>';
		}
		self::$summary[] = self::dump($msg, $data, $timerName);
		if (self::$reportMode > self::SHOW_ERRORS) {
			echo '</b>';
		}
	}



	/**
	 * Get summary
	 *
	 * @return array
	 */
	public static function getSummary()
	{
		return self::$summary;
	}



	/**
	 * Get timers
	 *
	 * @return array
	 */
	public static function getTimers()
	{
		self::$timers['time_total'] = array_sum(self::$timers);
		return self::$timers;
	}



	/**
	 * Get total time in s
	 *
	 * @return number
	 */
	public static function getTotalTime()
	{
		return microtime(true) - self::$startTime;
	}



	/**
	 * Format time
	 *
	 * @param int $time in seconds
	 * @return string
	 */
	public static function formatTime($time)
	{
		return number_format($time, 3, ',', '.') . 's ';
	}



	/**
	 * Format size
	 *
	 * @param int  $size in bytes
	 * @param bool $unit
	 * @return string $unit {@see self::$bytesUnit}
	 */
	public static function formatSize($size, $unit = false)
	{
		$formatTo = $unit ? $unit : self::$bytesUnit;
		if ($formatTo == self::SIZE_AUTO) {
			$formatTo = $size >= self::SIZE_MB ? self::SIZE_MB
				: ($size >= self::SIZE_KB ? self::SIZE_KB : self::SIZE_B);
		}
		return number_format(
			$size / $formatTo,
			$formatTo == self::SIZE_B ? 0 : 2,
			',',
			'.'
		) . self::$units[$formatTo];
	}



	/**
	 * Format status info
	 *
	 * @param string $timerName
	 * @return string Time in seconds and memory usage in {@see self::$bytesUnit}
	 */
	public static function status($timerName = null)
	{
		$time = microtime(true) - self::$time;
		if ($timerName) {
			self::$timers[$timerName] = $time - self::$lastIntervalTime;
			self::$lastIntervalTime = $time;
		}
		return
			self::formatTime($time) . self::formatSize(memory_get_usage(true))
			. ' => ';
	}
}
