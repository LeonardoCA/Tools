<?php
/**
 * This file is part of the LeonardoCA\Tools Nette Add-on
 * Copyright (c) 2012 Leonard Odlozilik
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace LeonardoCA\Tools;

use Nette;
use Nette\Application\Application;
use Nette\Application\IResponse;
use Nette\Application\UI\Presenter;
use Tracy\Debugger;

/**
 * AjaxPanels - refreshes content of debug panels
 *
 * @author    Vojtěch Dobeš
 * @author    Leonard Odlozilik
 */
class AjaxPanels extends Nette\Object
{

	/**
	 * Adds barDump for Ajax Requests
	 *
	 * @param Application $sender
	 * @param IResponse   $response
	 * @return void
	 */
	public function barDump(Application $sender, IResponse $response)
	{
		/** @var $presenter Presenter */
		$presenter = $sender->getPresenter();
		if ($presenter->isAjax() && Debugger::isEnabled()) {
			$bar = Debugger::getBar();
			$presenter->payload->netteDumps = $bar->getPanel('Tracy\Debugger:dumps')->data;
//			if ($dibiPanel = $bar->getPanel('DibiNettePanel')) {
//				$presenter->payload->dibiPanel = $dibiPanel->getPanel();
//			}
		}
	}



	/**
	 * @param Application $sender
	 * @param \Exception  $e
	 */
	public static function sendExceptionLogFile(
		Nette\Application\Application $sender,
		\Exception $e
	) {
		/** @var $presenter Presenter */
		if (Debugger::isEnabled()
			&& Debugger::getBar()
			&& ($presenter = $sender->getPresenter())
			&& (preg_match('/exceptionLogFile/', $e->getMessage()))
		) {
			echo file_exists($filename = $presenter->getParameter('file'))
				? $presenter->payload->dibiPanel = file_get_contents($filename)
				: '"' . $presenter->getParameter('file') . '" not found.';
			//Debugger::$bar = false;
			die();
		}
	}
}
