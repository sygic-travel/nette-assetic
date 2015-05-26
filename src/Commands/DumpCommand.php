<?php

namespace Tripomatic\NetteAssetic\Commands;

use Assetic\AssetManager;
use Assetic\AssetWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
	/** @var AssetManager */
	private $assetManager;

	/** @var AssetWriter */
	private $assetWriter;

	public function __construct(
		AssetManager $assetManager,
		AssetWriter $assetWriter
	) {
		$this->assetManager = $assetManager;
		$this->assetWriter = $assetWriter;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('assetic:dump')
			->setDescription('Dump assets to document root');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('Dumping assets:');
		foreach ($this->assetManager->getNames() as $name) {
			$asset = $this->assetManager->get($name);
			$this->assetWriter->writeAsset($asset);
			$output->writeln("    <info>$name</info> -> <info>{$asset->getTargetPath()}</info>");
		}

		$count = count($this->assetManager->getNames());
		$output->writeln('');
		$output->writeln("Successfully dumped <info>{$count}</info> assets.");
	}
}
