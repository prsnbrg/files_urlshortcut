<?php
/**
 * Files_URLShortcut
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Paul Rosenberg <dev@paulrosenberg.de>
 * @copyright Paul Rosenberg 2024
 */

namespace OCA\Files_URLShortcut\AppInfo;

use OCA\Files_URLShortcut\Controller\FileHandlingController;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use OCP\Util;

class Application extends App {
	/**
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = []) {
		parent::__construct('files_urlshortcut', $urlParams);

		$container = $this->getContainer();
		$server = $container->getServer();

		$container->registerService('FileHandlingController', function (IAppContainer $c) use ($server) {
			return new FileHandlingController(
				$c->getAppName(),
				$server->getRequest(),
				$server->getL10N($c->getAppName()),
				$server->getLogger(),
				$server->getShareManager(),
				$server->getUserSession(),
				$server->getRootFolder()
			);
		});
	}

	private function registerEventHooks(): void {
		$container = $this->getContainer();
		$eventDispatcher = $container->getServer()->getEventDispatcher();
		$callback = function () {
			Util::addscript('files_urlshortcut', 'editor');
		};

		$eventDispatcher->addListener(
			'OCA\Files::loadAdditionalScripts',
			$callback
		);
		$eventDispatcher->addListener(
			'OCA\Files_Sharing::loadAdditionalScripts',
			$callback
		);
	}

	public function initialize(): void {
		$this->registerEventHooks();
	}
}
