# LeonardoCA/Tools [![Build Status](https://secure.travis-ci.org/LeonardoCA/Tools.png)](https://travis-ci.org/LeonardoCA/Tools.svg)

Various tools for debugging nette applications:

- SmartDump
- htmlDump
- ProcessMonitor
- DumpMailer


## Installation

The best way to install LeonardoCA/Tools is using Composer:

```sh
composer require leonardoca/tools:@dev
```


## Configure extension in config.ini

```yml
extensions:
	smartDump: LeonardoCA\Tools\DI\SmartDumpExtension
smartDump:
	traceMode: 2  #false|number|all
```

## Usage

### Fast smart dump to Tracy Bar Panel

```php
	SmartDump::dump($variable);

	// shortcut
	sdump($variable);

	// add own html block to dump panel

	SmartDump::addToDumpPanel(
		'
		<table>
			<tr><th>A</th><th>B</th><th>C</th><th>D</th></tr>
			<tr><td>1</td><td>2</td><td>3</td><td>4</td></tr>
		</table>
		',
		'custom info'
	);

	// catching output buffering to dumps panel

	bs();
	echo "some output to browser";
	be('catch output buffering');

	// tidy formatted output

	$someXML =
		'<?xml version="1.0" encoding="utf-8"?><something>jjj</something>
<otherwise><x1>88</x1><x2>44</x2></otherwise>';

	sdump(tidyFormatXML($someXML));

	SmartDump::addToDumpPanel(
		'<pre>' . htmlspecialchars(tidyFormatXML($someXML)) . '</pre>',
		"some XML"
	);

	// dumpHtml for fast debugging of html markup of Nette\Utils\Html or whole Controls

	$someHtmlObject =
		Nette\Utils\Html::el('a')->addClass('btn btn-danger')->setTitle(
			'Danger'
		)->setHtml('danger');

	dumpHtml($someHtmlObject);

```
