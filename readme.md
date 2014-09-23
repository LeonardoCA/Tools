# LeonardoCA:Tools [![Build Status](https://secure.travis-ci.org/LeonardoCA/Tools.png)](https://travis-ci.org/LeonardoCA/Tools.svg)

Various tools for debugging nette applications:

- SmartDump
- ProcessMonitor
- htmlDump
- DumpMailer


## Installation

The best way to install LeonardoCA/Tools is using Composer:

```sh
composer require leonardoca/tools:@dev
```


## Configure extension in config.ini

example:

```yml
extensions:
	smartDump: LeonardoCA\Tools\DI\SmartDumpExtension
smartDump:
	traceMode: 2  #false|number|all
```

## Usage

### Fast smart dump to Tracy Bar Panel

```php
	sdump($variable);
```
