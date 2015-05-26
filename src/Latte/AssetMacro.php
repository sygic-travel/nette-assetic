<?php

namespace Tripomatic\NetteAssetic\Latte;

use Assetic\AssetManager;
use Latte\IMacro;
use Latte\MacroNode;

class AssetMacro implements IMacro
{
	/** @var AssetManager */
	private $assetManager;

	public function __construct(AssetManager $assetManager)
	{
		$this->assetManager = $assetManager;
	}

	public function initialize()
	{
	}

	public function finalize()
	{
	}

	public function nodeOpened(MacroNode $node)
	{
		$assetName = $node->args;
		$asset = $this->assetManager->get($assetName);

		$node->isEmpty = TRUE;
		$node->openingCode = $asset->getTargetPath();
	}

	public function nodeClosed(MacroNode $node)
	{
	}
}
