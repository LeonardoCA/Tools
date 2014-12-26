<?php
/**
 * This file is part of the LeonardoCA\Tools Nette Ad-don
 * Copyright (c) 2012 Leonard Odlozilik
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace LeonardoCA\Tools\DI;

use Nette\DI;

class AjaxPanelsCompilerExtension extends DI\CompilerExtension
{

	/**
	 * @var array
	 */
	public $defaults = array(
		'debugger' => '%debugMode%',
	);



	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if ($builder->hasDefinition('application')) {
			$ajaxPanels = $builder->addDefinition($this->prefix('panels'))
				->setClass('LeonardoCA\Tools\AjaxPanels');

			if ($config['debugger'] && interface_exists('Tracy\IBarPanel')) {
				$builder->addDefinition($this->prefix('jsonPanel'))
					->setClass('LeonardoCA\Tools\Diagnostics\AjaxJsonPanel');
				$ajaxPanels->addSetup('?->register(?)', array($this->prefix('@jsonPanel'), '@self'));
			}

			$builder->getDefinition('application')
				->addSetup(
					'$service->onResponse[] = ?;',
					array(array($ajaxPanels, 'barDump'))
				)
				// nasty workaround because I was not able to addComponent to presenter using compiler extension
				// file is sent while Presenter can not create component 'exceptionLogFile'
				->addSetup(
					'$service->onError[] = ?;',
					array(array($ajaxPanels, 'sendExceptionLogFile'))
				);
		}
	}
}
