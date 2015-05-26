# Tripomatic\NetteAssetic

A lightweight [Assetic](https://github.com/kriswallsmith/assetic) integration in [Nette Framework](http://nette.org).

Tripomatic\NetteApi integrates Assetic in Nette Framework in a minimalist way that allows using Assetic features without limitations. Configuration is fully compatible with the official Assetic package - no additional unnecessary conventions are introduced. Tripomatic\NetteApi also integrates asset routing for development environments and provides commands for asset dumping during production deployment.

## Installation

Install Tripomatic\NetteAssetic using [Composer](https://getcomposer.org):
```Shell
$ composer require tripomatic/nette-assetic
```

## Quickstart
Add NetteAssetic extension in your [NEON config](http://doc.nette.org/en/2.3/configuring):
```YAML
extensions:
  	assetic: Tripomatic\NetteAssetic\DI\AsseticExtension
```

Configure assets in corresponding extension's section:
```YAML
assetic:
	assets:
		cssLibs:
			files:
				- %appDir%/../vendor/bower-assets/bootstrap/dist/css/bootstrap.min.css
				- %appDir%/../vendor/bower-assets/bootstrap-select/dist/css/bootstrap-select.min.css

		cssDefault:
			files:
				- %appDir%/../document_root/css/main.css

		jsLibs:
			files:
				- %appDir%/../document_root/bower-assets/jquery/dist/jquery.js
				- %appDir%/../document_root/bower-assets/bootstrap/dist/js/bootstrap.js

		jsDefault:
			files:
				- %appDir%/../document_root/js/main.js
```

Include assets in your [Latte](http://latte.nette.org) template:
```Latte
<link rel="stylesheet" href="{asset cssLibs}">
<link rel="stylesheet" href="{asset cssDefault}">

<script src="{asset jsLibs}"></script>
<script src="{asset jsDefault}"></script>
```

## Configuration
Tripomatic\NetteAssetic provides easy and transparent configuration which is compatible with [Assetic](https://github.com/kriswallsmith/assetic)'s settings.

### Debug mode
Debug mode is set up automatically according to your application's `debugMode`. This behavior can be overriden:
```YAML
assetic:
	debug: TRUE # or FALSE to disable debug mode
```

### Asset routing
In debug mode assets are automatically compiled and served to the ouptut with [`AssetRoute`](src/Application/AssetRoute.php). This behavior can be overriden:

```YAML
assetic:
	route: TRUE # or FALSE to disable asset routing
```

### Filters
[Assetic filters](https://github.com/kriswallsmith/assetic#filters) are fully supported. At first filters must be registered in a similar way as common services:

```YAML
assetic:
	filters:
		lessPhp: Assetic\Filter\LessphpFilter
		yuiCss: Assetic\Filter\Yui\CssCompressorFilter('/path/to/yuicompressor.jar')
```

Then they can be assigned to assets:
```YAML
assetic:
	assets:
		assetName:
			files:
				- %appDir%/../document_root/css/front.less
				- %appDir%/../document_root/css/admin.less
			filters:
				- lessPhp
				- ?yuiCss
```
Prefixing a filter name with `?` cause the filter to be omitted in debug mode.

### Workers
[Assetic workers](https://github.com/kriswallsmith/assetic/blob/master/src/Assetic/Factory/Worker/WorkerInterface.php) are fully supported. All assets will be passed to the worker's `process()` method. Typical example is the [CacheBustingWorker](https://github.com/kriswallsmith/assetic#cache-busting):

```YAML
assetic:
	workers:
		- Assetic\Factory\Worker\CacheBustingWorker	
```

### Cache
Asset compiling and serving can be computationally expensive. Therefore, assets are cached and recompiled only when changed. By default assets are cached only in-memory with `Assetic\Cache\ArrayCache`. It's better to use a persistent cache that can keep data between two requests:
```YAML
assetic:
	cache: Assetic\Cache\FilesystemCache(%tempDir%/assetic)
```
For a full list of cache implementations see https://github.com/kriswallsmith/assetic/tree/master/src/Assetic/Cache.

[`AssetRoute`](src/Application/AssetRoute.php) also automatically handles HTTP caching by using the correct cache-control headers. If an asset has not been modified the client receives `HTTP/1.1 304 Not Modified`.

### Asset dumping
Although content and HTTP caching mechanisms can significantly speed up asset serving, there is still an overhead of running the application for every asset. Thus in production environments the assets should be dumped to a filesystem or CDN during deploy and served directly by HTTP server or CDN network.

The easiest option to dump all assets to their target location is using provided [`Symfony\Console`](https://github.com/symfony/Console) command. NetteAssetic automatically registers the command in DI container so all that has to be done is to register the command to your console application instance:
```php
$commands = $container->findByType('Symfony\Component\Console\Command\Command');
foreach ($commands as $commandName) {
	$consoleApplication->add($container->getService($commandName));
}
```

Then the assets can be dumped with (supposing you run the Symfony\Console application from `console.php`):
```Shell
$ php console.php assetic:dump
```
Don't forget to add the above line to your deploy scripts.

### List of configuration options
Listed below are all possible configuration options along with some example values:

```YAML
assetic:
	root: %wwwDir%
	output: assetic/*
	debug: %debugMode%
	route: TRUE
	cache: Assetic\Cache\FilesystemCache(%tempDir%/assetic)
	filters:
		lessPhp: Assetic\Filter\LessphpFilter
		yuiCss: Assetic\Filter\Yui\CssCompressorFilter('/path/to/yuicompressor.jar')
	workers:
		- Assetic\Factory\Worker\CacheBustingWorker
	assets:
		assetName:
			files:
				- %appDir%/../document_root/css/front.less
				- %appDir%/../document_root/css/admin.less
			filters:
				- lessPhp
				- ?yuiCss
			output: assetic/*
			root: %wwwDir%
			debug: TRUE
			variables: []
```

## FAQ

#### How do I include Bootstrap fonts or images?

For now you have to list them as assets with fixed output (Assetic itself doesn't have a better option for this):

```YAML
assetic:
	assets:
		bootstrap_glyphicons_eot: [ files: %appDir%/../vendor/bower-assets/bootstrap/dist/fonts/glyphicons-halflings-regular.eot, output: fonts/glyphicons-halflings-regular.eot ]
		bootstrap_glyphicons_svg: [ files: %appDir%/../vendor/bower-assets/bootstrap/dist/fonts/glyphicons-halflings-regular.svg, output: fonts/glyphicons-halflings-regular.svg ]
		bootstrap_glyphicons_ttf: [ files: %appDir%/../vendor/bower-assets/bootstrap/dist/fonts/glyphicons-halflings-regular.ttf, output: fonts/glyphicons-halflings-regular.ttf ]
		bootstrap_glyphicons_woff: [ files: %appDir%/../vendor/bower-assets/bootstrap/dist/fonts/glyphicons-halflings-regular.woff, output: fonts/glyphicons-halflings-regular.woff ]
		bootstrap_glyphicons_woff2: [ files: %appDir%/../vendor/bower-assets/bootstrap/dist/fonts/glyphicons-halflings-regular.woff2, output: fonts/glyphicons-halflings-regular.woff2 ]
```

## License
Tripomatic\NetteAssetic is licensed under [MIT](LICENSE.md).
