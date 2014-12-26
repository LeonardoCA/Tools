<?php
/**
 * This file is part of the LeonardoCA\Tools Nette Ad-don
 * Copyright (c) 2012 Leonard Odlozilik
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace LeonardoCA\Tools\Diagnostics;

use LeonardoCA\Tools\AjaxPanels;
use Nette;
use Tracy;
use Tracy\Debugger;

/**
 * AjaxJsonPanel - refreshes content of any debug panel
 *
 * @author Leonard Odlozilik
 */
class AjaxJsonPanel extends Nette\Object implements Tracy\IBarPanel
{
	/** @var AjaxPanels */
	private $panels;

	/**
	 * Renders HTML code for custom tab.
	 *
	 * @return string
	 */
	public function getTab()
	{
		return '<span title="Ajax">Ajax</span>';
	}



	/**
	 * Renders HTML code for custom panel.
	 *
	 * @return string
	 */
	public function getPanel()
	{
		ob_start();
		require __DIR__ . '/bar.ajaxjson.panel.phtml';
		return ob_get_clean();
	}



	public function register(AjaxPanels $panels)
	{
		$this->panels = $panels;
		Debugger::getBar()->addPanel($this, 'ajaxPanels.jsonPanel');
		return $this;
	}
}
