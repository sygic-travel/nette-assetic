<?php

namespace Tripomatic\NetteAssetic\Application;

use Assetic\Asset\AssetInterface;
use Assetic\AssetManager;
use Nette\Application;
use Nette\Http;

class AssetRoute implements Application\IRouter
{
	/** @var AssetManager */
	private $assetManager;

	private static $extensionsToMimeTypes = [
		'ai' => 'application/postscript',
		'bmp' => 'image/bmp',
		'cab' => 'application/vnd.ms-cab-compressed',
		'css' => 'text/css',
		'doc' => 'application/msword',
		'eot' => 'application/vnd.ms-fontobject',
		'eps' => 'application/postscript',
		'exe' => 'application/x-msdownload',
		'flv' => 'video/x-flv',
		'gif' => 'image/gif',
		'htm' => 'text/html',
		'html' => 'text/html',
		'ico' => 'image/vnd.microsoft.icon',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'mov' => 'video/quicktime',
		'mp3' => 'audio/mpeg',
		'msi' => 'application/x-msdownload',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		'odt' => 'application/vnd.oasis.opendocument.text',
		'otf' => 'application/x-font-opentype',
		'pdf' => 'application/pdf',
		'php' => 'text/html',
		'png' => 'image/png',
		'ppt' => 'application/vnd.ms-powerpoint',
		'ps' => 'application/postscript',
		'psd' => 'image/vnd.adobe.photoshop',
		'qt' => 'video/quicktime',
		'rar' => 'application/x-rar-compressed',
		'rtf' => 'application/rtf',
		'sfnt' => 'application/font-sfnt',
		'svg' => 'image/svg+xml',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',
		'swf' => 'application/x-shockwave-flash',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'ttf' => 'application/x-font-truetype',
		'txt' => 'text/plain',
		'woff' => 'application/font-woff',
		'woff2' => 'application/font-woff2',
		'xls' => 'application/vnd.ms-excel',
		'xml' => 'application/xml',
		'zip' => 'application/zip',
	];

	public function __construct(
		AssetManager $assetManager
	) {
		$this->assetManager = $assetManager;
	}

	public function match(Http\IRequest $httpRequest)
	{
		$path = $httpRequest->getUrl()->getPath();

		foreach ($this->assetManager->getNames() as $name) {
			$asset = $this->assetManager->get($name);
			if ('/' . $asset->getTargetPath() === $path) {
				$this->dumpAsset($asset);
			}
		}
	}

	public function constructUrl(Application\Request $appRequest, Http\Url $refUrl)
	{
	}

	private function dumpAsset(AssetInterface $asset)
	{
		// HTTP caching
		$mtime = gmdate('D, d M y H:i:s', $asset->getLastModified()) . 'GMT';
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $mtime = $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
			header('HTTP/1.1 304 Not Modified');
			exit();
		}

		// set headers & dump asset
		$extension = pathinfo($asset->getTargetPath(), PATHINFO_EXTENSION);
		if (array_key_exists($extension, self::$extensionsToMimeTypes)) {
			$mimeType = self::$extensionsToMimeTypes[$extension];
			header("Content-Type: $mimeType");
			header("Last-Modified: $mtime");
			header('Cache-Control: public');
		}
		echo $asset->dump();
		exit();
	}
}
