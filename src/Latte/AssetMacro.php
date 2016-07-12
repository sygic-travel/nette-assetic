<?php

namespace Tripomatic\NetteAssetic\Latte;

use Latte\IMacro;
use Latte\MacroNode;

class AssetMacro implements IMacro
{
	public function initialize()
	{
	}

	public function finalize()
	{
	}

	public function nodeOpened(MacroNode $node)
	{
		$assetName = $node->args;

		$node->isEmpty = TRUE;
		$node->openingCode = '<?php echo call_user_func($this->filters->_asset, "' . $assetName . '"); ?>';
	}

	public function nodeClosed(MacroNode $node)
	{
	}
}
