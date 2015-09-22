<?php

namespace Tripomatic\NetteAssetic\Latte;

use Assetic\AssetManager;

class AssetFilter
{
	/** @var AssetManager */
	private $assetManager;

	public function __construct(AssetManager $assetManager)
	{
		$this->assetManager = $assetManager;
	}

	/**
	 * @param string $assetName
	 * @return string
	 */
	public function __invoke($assetName)
	{
		$asset = $this->assetManager->get($assetName);

		return $asset->getTargetPath();
	}
}
