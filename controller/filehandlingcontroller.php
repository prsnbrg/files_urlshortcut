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

namespace OCA\Files_URLShortcut\Controller;

use OC\HintException;
use OC\User\NoUserException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Constants;
use OCP\Files\File;
use OCP\Files\ForbiddenException;
use OCP\Files\IRootFolder;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Share\IManager;

use Sabre\DAV\Exception\NotFound;

//use Firebase\JWT\JWT;

//require_once __DIR__ . '/../vendor/autoload.php';

class FileHandlingController extends Controller {
	/** @var IL10N */
	private $l;

	/** @var ILogger */
	private $logger;

	/** @var IManager */
	private $shareManager;

	/** @var IUserSession */
	private $userSession;

	/** @var IRootFolder */
	private $root;

	/**
	 * @NoAdminRequired
	 *
	 * @param string $AppName
	 * @param IRequest $request
	 * @param IL10N $l10n
	 * @param ILogger $logger
	 * @param IManager $shareManager
	 * @param IUserSession $userSession
	 * @param IRootFolder $root
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		IL10N $l10n,
		ILogger $logger,
		IManager $shareManager,
		IUserSession $userSession,
		IRootFolder $root
	) {
		parent::__construct($AppName, $request);
		$this->l = $l10n;
		$this->logger = $logger;
		$this->shareManager = $shareManager;
		$this->userSession = $userSession;
		$this->root = $root;
	}

	/**
	 * load text file
	 *
	 * @NoAdminRequired
	 * @NoSubadminRequired
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $dir
	 * @param string $filename
	 * @return DataResponse
	 */
	public function load($dir, $filename) {
		try {
			if (!empty($filename)) {
				$path = $dir . '/' . $filename;
				try {
					$node = $this->getNode($path);
				} catch (NoUserException $e) {
					return new DataResponse(
						['message' => $this->l->t('No user found')],
						Http::STATUS_BAD_REQUEST
					);
				}

				/** @var mixed $fileContents */
				$fileContents = $node->getContent();
				
				if ($fileContents !== false) {
					$arrayoffile = explode("\r\n", $fileContents);
					for($i=0; $i < count($arrayoffile); $i++) {
						if(strpos($arrayoffile[$i], "URL=") === 0) {
							$url = substr($arrayoffile[$i], 4);

							return new DataResponse(
								[
									'filecontents' => $url,
								],
								Http::STATUS_OK
							);
						}
					}
				} else {
					return new DataResponse(['message' => (string)$this->l->t('Cannot read the file.')], Http::STATUS_BAD_REQUEST);
				}
			} else {
				return new DataResponse(['message' => (string)$this->l->t('Invalid file path supplied.')], Http::STATUS_BAD_REQUEST);
			}
		} catch (ForbiddenException $e) {
			return new DataResponse(['message' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		} catch (HintException $e) {
			$message = (string)$e->getHint();
			return new DataResponse(['message' => $message], Http::STATUS_BAD_REQUEST);
		} catch (\Exception $e) {
			$message = (string)$this->l->t('An internal server error occurred.');
			return new DataResponse(['message' => $message], Http::STATUS_BAD_REQUEST);
		}
	}


	private function getNode(string $path): File {
		$sharingToken = $this->request->getParam('sharingToken');

		if ($sharingToken) {
			$share = $this->shareManager->getShareByToken($sharingToken);
			$node = $share->getNode();
			if (!($node instanceof File)) {
				$node = $node->get($path);
			}
		} else {
			$user = $this->userSession->getUser();
			if (!$user) {
				throw new NoUserException();
			}

			$node = $this->root->get('/' . $user->getUID() . '/files' . $path);
		}

		if (!($node instanceof File)) {
			throw new NotFound();
		}

		return $node;
	}
}
