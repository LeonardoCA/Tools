<?php
/**
 * This file is part of LeonardoCA\Tools for Nette Framework
 * Copyright (c) 2012 Leonard Odložilík
 * For the full copyright and license information,
 * please view the file license.txt that was distributed with this source code.
 */

namespace LeonardoCA\Tools;

use Nette;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

/**
 * Dump Mailer
 *
 * @author Leonard Odložilík
 */
class DumpMailer extends Nette\Object implements IMailer
{

	public function send(Message $mail)
	{
		SmartDump::sdump($mail->headers, 'Email');
		SmartDump::addToDumpPanel(
			shadowDomTemplate(
				'Email',
				dumpHtml((string)$mail->htmlBody, true, 0, true),
				prettyPrintStyle()
			)
		);
	}

}
