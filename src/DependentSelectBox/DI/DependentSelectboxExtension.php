<?php

namespace DependentSelectBox\DI;

use DependentSelectBox\DependentSelectBox;
use DependentSelectBox\FormControlDependencyHelper;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

class DependentSelectboxExtension extends CompilerExtension
{
	const BUTTON_SUFIX = '_submit';
	const CLASS_NAME = DependentSelectBox::class;
	const EXTENSION_NAME = 'dependentSelectbox';
	const METHOD_NAME = 'addDependentSelectBox';

	/** Config keys */
	const CONFIG_BUTTON_SUFIX = 'buttonSuffix';
	const CONFIG_DEFAULT_BUTTON_POSITION = 'defaultButtonPosition';
	const CONFIG_METHOD_NAME = 'methodName';

	/** @var array */
	public $defaults = array(
		self::CONFIG_BUTTON_SUFIX => self::BUTTON_SUFIX,
		self::CONFIG_DEFAULT_BUTTON_POSITION => FormControlDependencyHelper::POSITION_BEFORE_CONTROL,
		self::CONFIG_METHOD_NAME => self::METHOD_NAME,
	);

	/**
	 * Adjusts DI container compiled to PHP class. Intended to be overridden by descendant.
	 *
	 * @return void
	 */
	public function afterCompile(ClassType $class)
	{
		parent::afterCompile($class);
		$config = $this->getConfig($this->defaults);
		$init = $class->methods['initialize'];
		$init->addBody(self::CLASS_NAME . '::register(?);', array($config[self::CONFIG_METHOD_NAME]));
		$init->addBody(FormControlDependencyHelper::class . '::$buttonSuffix = ?;', array($config[self::CONFIG_BUTTON_SUFIX]));
		$init->addBody(FormControlDependencyHelper::class . '::$defaultButtonPosition = ?;', array($config[self::CONFIG_DEFAULT_BUTTON_POSITION]));
	}

	/**
	 * Posibility to register extension staticaly in the bootstrap
	 *
	 * @param Configurator $configurator
	 * @return void
	 */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension(self::EXTENSION_NAME, new self);
		};
	}
}
