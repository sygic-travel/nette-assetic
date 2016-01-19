<?php

namespace Tripomatic\NetteAssetic\DI;

use Nette\DI;
use Nette\PhpGenerator;

class AsseticExtension extends DI\CompilerExtension
{
	private static $configDefaults = [
		'root' => '%wwwDir%',
		'output' => 'assetic/*',
		'debug' => '%debugMode%',
		'route' => NULL,
		'filters' => [],
		'workers' => [],
		'cache' => 'Assetic\Cache\ArrayCache',
		'assets' => [],
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig(self::$configDefaults);

		$builder->addDefinition($this->prefix('assetManager'))
			->setClass('Assetic\AssetManager');

		$builder->addDefinition($this->prefix('filterManager'))
			->setClass('Assetic\FilterManager');

		$builder->addDefinition($this->prefix('assetFactory'))
			->setClass('Assetic\Factory\AssetFactory', [$config['root']])
			->addSetup('setAssetManager')
			->addSetup('setFilterManager')
			->addSetup('setDefaultOutput', [$config['output']])
			->addSetup('setDebug', [$config['debug']]);

		$builder->addDefinition($this->prefix('assetWriter'))
			->setClass('Assetic\AssetWriter', [$config['root']]);

		// filters
		$filterServices = [];
		foreach ($config['filters'] as $name => $filter) {
			$serviceName = $this->prefix("filter.$name");
			$filterServices[$serviceName] = $filter;
			$builder->getDefinition($this->prefix('filterManager'))
				->addSetup('set', [$name, "@$serviceName"]);
		}
		$this->compiler->parseServices($builder, ['services' => $filterServices]);

		// workers
		$workerServices = [];
		foreach ($config['workers'] as $name => $worker) {
			$serviceName = $this->prefix("worker.$name");
			$workerServices[$serviceName] = $worker;
			$builder->getDefinition($this->prefix('assetFactory'))
				->addSetup('addWorker', ["@$serviceName"]);
		}
		$this->compiler->parseServices($builder, ['services' => $workerServices]);

		// cache
		$this->compiler->parseServices($builder, ['services' => [$this->prefix('cache') => $config['cache']]]);

		// macros & filters
		$builder->addDefinition($this->prefix('latte.assetMacro'))
			->setClass('Tripomatic\NetteAssetic\Latte\AssetMacro');

		$builder->addDefinition($this->prefix('latte.assetFilter'))
			->setClass('Tripomatic\NetteAssetic\Latte\AssetFilter');

		$builder->getDefinition('nette.latteFactory')
			->addSetup('addMacro', ['asset', '@' . $this->prefix('latte.assetMacro')])
			->addSetup('addFilter', ['_asset', '@' . $this->prefix('latte.assetFilter')]);

		// Symfony\Console commands
		if (class_exists('Symfony\Component\Console\Command\Command')) {
			$builder->addDefinition($this->prefix('commands.dumpCommand'))
				->setClass('Tripomatic\NetteAssetic\Commands\DumpCommand');
		}
	}

	public function afterCompile(PhpGenerator\ClassType $class)
	{
		$config = $this->getConfig(self::$configDefaults);
		$initialize = $class->methods['initialize'];

		// create assets
		foreach ($config['assets'] as $name => $asset) {
			$initialize->addBody('$asset = $this->getService(?)->createAsset(?, ?, ?);', [
				$this->prefix('assetFactory'),
				$asset['files'],
				isset($asset['filters']) ? $asset['filters'] : [],
				[
					'name' => $name,
					'vars' => isset($asset['variables']) ? $asset['variables'] : [],
				]
			]);

			if (isset($asset['output'])) {
				$initialize->addBody('$asset->setTargetPath(?);', [$asset['output']]);
			}

			$initialize->addBody('$this->getService(?)->set(?, new \Assetic\Asset\AssetCache($asset, $this->getService(?)));', [
				$this->prefix('assetManager'),
				$name,
				$this->prefix('cache'),
			]);
		}

		// add asset router
		$route = $config['route'] === NULL ? $config['debug'] === TRUE : $config['route'];
		if ($route) {
			// route is prepended to be the first route in route list
			$routePrependCode = '
				$router = $this->getService(?);
				$router[] = $route = new \Tripomatic\NetteAssetic\Application\AssetRoute($this->getService(?));
				for ($i = $router->count() - 1; $i > 0 ; $i--) { $router[$i] = $router[$i - 1]; };
				$router[0] = $route;
			';
			$initialize->addBody($routePrependCode, [
				'routing.router',
				$this->prefix('assetManager'),
			]);
		}
	}
}
