<?php
/**
 * This file is part of the LeonardoCA\Spy
 * Copyright (c) 2012 Leonard Odlozilik (leonard.odlozilik@gmail.com)
 * For the full copyright and license information,
 * please view the file license.txt that was distributed with this source code.
 */
namespace LeonardoCA\Tools\DI;

use Nette;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Configurator;

/**
 * SmartDumpExtension
 * Registers SmartDump on Application->onShutdown event
 *
 * @author Leonard Odložilík
 */
class SmartDumpExtension extends CompilerExtension
{

	/** @var array Default setting */
	public $defaults = array(
		'traceMode' => 1,
		'showParams' => false,
		'shortcut' => 'sdump' // @todo add possibility to change shortcut name
	);



	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);
		$initialize = $class->methods['initialize'];
		// Add shortcut - it must be defined even in production if some dumps are left in source code
		$initialize->addBody(
			"require_once '" . dirname(__FILE__)
			. "/../shortcuts/smartDump.php';"
		);
		$initialize->addBody(
			"require_once '" . dirname(
				__FILE__
			) . "/../shortcuts/processMonitor.php';"
		);
		if (!$container->parameters['debugMode']) {
			return;
		}
		$initialize->addBody(
			'LeonardoCA\Tools\SmartDump::$? = ?;',
			array('traceMode', $config['traceMode'])
		);
		if ($config['showParams']) {
			$initialize->addBody(
				'$this->getService(?)->onRequest[]=callback("LeonardoCA\Tools\SmartDump::dumpRequest");',
				array('application')
			);
		}
		$initialize->addBody(
			'$this->getService(?)->onShutdown[]=callback("LeonardoCA\Tools\SmartDump::output");',
			array('application')
		);
		$container->addDefinition($this->prefix('dumpMailer'))
			->setImplement('IMailer')
			->setClass('LeonardoCA\Tools\DumpMailer')
			->setAutowired(false);
	}



	/**
	 * @param Configurator $config
	 */
	public static function register(Configurator $config)
	{
		/** @noinspection PhpUnusedParameterInspection */
		$config->onCompile[] =
			function (Configurator $configurator, Compiler $compiler) {
				$compiler->addExtension('smartDump', new SmartDumpExtension());
			};
	}

}
