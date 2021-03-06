<?php

/**
 * This file is part of LeonardoCA\Tools for Nette Framework
 * Copyright (c) 2012 Leonard Odložilík
 * For the full copyright and license information,
 * please view the file license.txt that was distributed with this source code.
 */

namespace LeonardoCATests\Tools;

use LeonardoCA\Tools\ProcessMonitor;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Leonard Odložilík
 */
class ProcessMonitorTest extends \Tester\TestCase
{

	public function testFormatSize()
	{
		Assert::same(
			'1,00MB',
			ProcessMonitor::formatSize(1024 * 1024, ProcessMonitor::SIZE_MB)
		);
		Assert::same(
			'1,00MB',
			ProcessMonitor::formatSize(1024 * 1024, ProcessMonitor::SIZE_AUTO)
		);
		Assert::same(
			'1.048.576B',
			ProcessMonitor::formatSize(1024 * 1024, ProcessMonitor::SIZE_B)
		);
		Assert::same(
			'1.024,00KB',
			ProcessMonitor::formatSize(1024 * 1024, ProcessMonitor::SIZE_KB)
		);
	}



	public function testFormatTime()
	{
		Assert::same('33,487s ', ProcessMonitor::formatTime(33.48654));
	}

}

$testCache = new ProcessMonitorTest;
$testCache->run();
