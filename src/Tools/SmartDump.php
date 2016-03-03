<?php
/**
 * This file is part of LeonardoCA\Tools for Nette Framework
 * Copyright (c) 2012 Leonard Odložilík
 * For the full copyright and license information,
 * please view the file license.txt that was distributed with this source code.
 */
namespace LeonardoCA\Tools;

use Nette\Application\Application;
use Nette\Application\Request;
use SplObjectStorage;
use Tracy\Debugger;
use Tracy\DefaultBarPanel;
use Tracy\Dumper;
use Tracy\Helpers;

/**
 * Smart Dump
 * - groups dumps by method calls and automatically ads title
 * - optionally displays trace info
 *
 * @author Leonard Odložilík
 */
class SmartDump
{

	/** @var bool Initialized */
	private static $initialized = false;

	/** @var SplObjectStorage Calls counter - used for grouping dumps */
	private static $callsCounter;

	/** @var array Dumps storage */
	private static $dumps;

	/** @var bool Dump Object - dump complete object in which is sdump called? */
	public static $dumpObject = false;

	/** @var bool Trace Mode - include trace info? */
	public static $traceMode = false;

	/** @var DefaultBarPanel */
	private static $dumpPanel;



	/**
	 * Dumps information about a variable in readable format.
	 *
	 * @param  mixed $var to dump
	 * @param null   $title
	 * @return void
	 */
	public static function sdump($var, $title = null)
	{
		if (!Debugger::$productionMode) {
			self::initialize();
			$trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
			$item = Helpers::findTrace($trace, 'sdump', $index)
				?: Helpers::findTrace($trace, __CLASS__ . '::sdump', $index);
			if (isset($item['file'], $item['line']) && is_file($item['file'])) {
				$lines = file($item['file']);
				preg_match(
					'#sdump\((.*)\)#',
					$lines[$item['line'] - 1],
					$matches
				);
				$params =
					isset($matches[1]) ? htmlspecialchars($matches[1]) : false;
				if ($params) {
					preg_match('#array\((.*)\)#', $params, $matches2);
					$params = isset($matches2[1]) ? $matches2[1] : $params;
					$params = explode(',', $params);
				}
				$dumpParams = (isset($matches[0]) ? "$matches[0];" : '');
				$location =
					Debugger::$showLocation ? "<br>" . Helpers::editorLink(
							$item['file'],
							$item['line']
						)
						: false;
			}
			$tiMethod = $trace[$index + 1];
			$dumpTitle = isset($tiMethod['class'])
				? $tiMethod['class'] . $tiMethod['type'] . $tiMethod['function']
				. '(); '
				: $tiMethod['function'] . $title;
			$dumpObject = self::$dumpObject && isset($tiMethod['object'])
				? Dumper::toHtml($tiMethod['object']) : '';
			$dump = (isset($dumpParams) ? "<b>$dumpParams</b> "
					: '') . (isset($location) ? $location : '') . $dumpObject;
			if (self::$traceMode) {
				for ($i = 0; $i <= $index; $i++) {
					array_shift($trace);
				}
				$dump .= "<br/>" . self::showTraceSimple($trace);
			}
			$dump .= Dumper::toHtml($var);
			self::$dumps = array('title' => $dumpTitle, 'dump' => $dump);
			self::addToDumpPanel($dump, $dumpTitle);
		}
	}



	/**
	 * Dump Request
	 *
	 * @param Application $application
	 * @param Request     $request
	 */
	public static function dumpRequest(
		/** @noinspection PhpUnusedParameterInspection */
		Application $application,
		Request $request
	) {
		self::sdump($request->parameters);
		self::sdump($request->post);
	}



	/**
	 * Get access to instance of Nette\Diagnostics\DumpPanel
	 */
	private static function getDumpPanel()
	{
		self::$dumpPanel = Debugger::getBar()->getPanel('Tracy\Debugger:dumps');
                if (!self::$dumpPanel) {
                        self::$dumpPanel = Debugger::getBar()->getPanel('Tracy\DefaultBarPanel');
                }
                if (!self::$dumpPanel) {
                        Debugger::barDump("initialize smartDump", NULL, [Dumper::LOCATION => false]);
                        self::$dumpPanel = Debugger::getBar()->getPanel('Tracy\DefaultBarPanel');
                }
	}



	/**
	 * Add html block to Dump Panel
	 * - useful for testing
	 *
	 * @param mixed  $block data
	 * @param string $title
	 */
	public static function addToDumpPanel($block, $title = '')
	{
		if (!Debugger::$productionMode) {
			self::initialize();
		}
		self::$dumpPanel->data[] = array('title' => $title, 'dump' => $block);
	}



	public static function output()
	{
		if (self::$dumps && !empty(self::$dumps)) {
			//foreach (self::$dumps as $dump) { self::addToDumpPanel($dump);} ???
		} else {
			self::initialize();
			if (empty(self::$dumpPanel->data)) {
				self::addToDumpPanel(
					'preparing Dumps Panel for AJAX',
					'SmartDump starts'
				);
			}
		}
	}



	/**
	 * Send dumps to BarDump
	 * - it needs to be sent on application shutdown to make grouping possible
	 */
	public static function toBarDump()
	{
		foreach (self::$dumps as $dump) {
			Debugger::barDump($dump);
		}
	}



	/**
	 * Sends dumps to page
	 * - in case of fatal error or other exceptions when debug bar will not be visible
	 */
	public static function toPage()
	{
		foreach (self::$dumps as $dump) {
			Debugger::dump($dump);
		}
	}



	/**
	 * Initialize
	 *
	 * @return void
	 */
	private static function initialize()
	{
		if (!self::$initialized) {
			self::$callsCounter = new SplObjectStorage();
			self::getDumpPanel();
			self::$initialized = true;
		}
	}



	/**
	 * Simple trace info
	 *
	 * @param array $stack
	 * @return string
	 */
	public static function showTraceSimple($stack)
	{
		$maxDepth = Debugger::$maxDepth;
		Debugger::$maxDepth = 1;
		$counter = 0;
		ob_start();
		foreach ($stack as $row) {
			try {
				$r = isset($row['class'])
					? new \ReflectionMethod($row['class'], $row['function'])
					: new \ReflectionFunction($row['function']);
				$params = $r->getParameters();
			} catch (\Exception $e) {
				$params = array();
			}
			$args = array();
			if (array_key_exists('args', $row)) {
				$argList = new \ArrayIterator($row['args']);
				while ($argList->valid()) {
					$key = $argList->key();
					$args[] =
						!isset($params[$key]) ?: '$' . $params[$key]->name;
					$argList->next();
				}
			}
			echo ++$counter . '. <b>'
				. (isset($row['class']) ? htmlspecialchars(
					$row['class'] . $row['type']
				) : '')
				. htmlspecialchars($row['function']) . '(' . implode(
					', ',
					$args
				) . ');</b> '
				. '<br>' . (isset($row['file']) && is_file($row['file'])
					? Helpers::editorLink($row['file'], $row['line'])
					:
					'<i>inner-code</i>' . isset($row['line']) ?: $row['line'])
				. '<br>';
			if ($counter == self::$traceMode) {
				break;
			}
		}
		Debugger::$maxDepth = $maxDepth;
		return ob_get_clean();
	}



	/**
	 * Editor link
	 *
	 * @param $file
	 * @param $line
	 * @return string
	 */
	public static function editorLink($file, $line)
	{
		return '<small>in <a href="editor://open/?file=' . rawurlencode($file)
		. "&amp;line=$line\" title = \"$file:$line\">" . htmlspecialchars(
			basename($file)
		) . ":$line</a></small>";
	}
}
