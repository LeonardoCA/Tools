<?php
/**
 * This file is part of the LeonardoCA\Tools Nette Add-on
 * Copyright (c) 2012 Leonard Odlozilik
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace LeonardoCA\Tools;

use Nette;
use Tracy\Debugger;

/**
 * Component sending log file
 *
 * @author Leonard Odlozilik
 */
class ComponentExceptionLogFile extends Nette\Application\UI\PresenterComponent
{
	public function handleGetExceptionLogFile()
	{
		if (Debugger::isEnabled() && Debugger::getBar()) {
			$presenter = $this->getPresenter(true);
			echo file_exists($filename = $presenter->getParameter('file'))
				? file_get_contents($filename) : '"' . $this->getParameter('file') . '" not found.';
			$presenter->terminate();
		}
	}
}
