<?php
/**
 * ownCloud - Files_URLShortcut
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Paul Rosenberg <dev@paulrosenberg.de>
 * @copyright Paul Rosenberg 2024
 */

namespace OCA\Files_URLShortcut\AppInfo;

return ['routes' => [
	[
		'name' => 'FileHandling#load',
		'url' => '/ajax/loadurl',
		'verb' => 'GET'
	]
]];