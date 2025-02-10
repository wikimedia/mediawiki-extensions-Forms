<?php

namespace MediaWiki\Extension\Forms\Target;

use Exception;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Config\HashConfig;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Forms\ITarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;

abstract class TitleTarget implements ITarget {
	/** @var array */
	protected $reservedFields = [ '_form', '_form_rev', '_id' ];

	/**
	 * @var string
	 */
	protected $pageName = '';

	/**
	 * @var string
	 */
	protected $form = '';

	/**
	 * Whether this page already exists
	 *
	 * @var bool
	 */
	protected $exists = false;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var string
	 */
	protected $summary = '';

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 * @param string $pageName
	 * @param string $form
	 * @param bool $exists
	 */
	protected function __construct( $pageName, $form, $exists ) {
		$this->pageName = $pageName;
		// Make sure operating page name is not suffixed
		$this->stripExtension();
		$this->form = $form;
		$this->exists = $exists;
		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 * @param HashConfig $config
	 * @return ITarget
	 */
	public static function factory( HashConfig $config ) {
		if ( !$config->has( 'title' ) || !$config->has( 'form' ) ) {
			return null;
		}
		$pageName = $config->get( 'title' );
		$exists = false;
		if ( $config->has( '_id' ) ) {
			$title = Title::newFromText( $config->get( '_id' ) );
			if ( $title instanceof Title &&
				$title->exists()
			) {
				$exists = true;
				$pageName = $title->getPrefixedDBkey();
			}
		}
		return new static( $pageName, $config->get( 'form' ), $exists );
	}

	/**
	 * @param array $formsubmittedData
	 * @param string $summary
	 * @return Status
	 */
	public function execute( $formsubmittedData, $summary ) {
		$this->data = $formsubmittedData;
		$this->summary = $summary;

		if ( !$this->exists ) {
			$this->parsePageName();
		}

		if ( !$this->checkPermissions() ) {
			return Status::newFatal( 'badaccess-group0' );
		}
		$this->addFormDataToPage();
		$this->addPageNameToData();
		return $this->saveToPage();
	}

	/**
	 * @return array
	 */
	public function getDefaultAfterAction() {
		return [
			"type" => "redirect",
			"url" => $this->getTitleFromPageName()->getFullURL()
		];
	}

	/**
	 * If form is defined on wiki as a wikipage, get the latest
	 * form RevID and update the form instance with it
	 */
	public function tryUpdateFormRevision() {
		$definitionManager = $this->services->getService( "FormsDefinitionManager" );
		if ( $definitionManager->definitionIsWikipage( $this->form ) ) {
			$this->data['_form_rev'] = $definitionManager->getLatestDefinitionRev( $this->form );
		}
	}

	/**
	 * @param bool $makeSureIsValid If true it will set page name that is surely creatable
	 */
	protected function parsePageName( $makeSureIsValid = true ) {
		$vars = $this->getPageNameVars();
		foreach ( $vars as $placeholder => $var ) {
			$replacement = isset( $this->data[$var] ) ? $this->data[$var] : '';
			if ( $var === '_form' ) {
				$replacement = $this->form;
			}
			if ( $var === '_user' ) {
				$user = RequestContext::getMain()->getUser();
				if ( $user->isAnon() ) {
					$replacement = '';
				} else {
					$replacement = $user->getName();
				}
			}
			if ( !empty( $replacement ) ) {
				$this->pageName = preg_replace( "/$placeholder/", $replacement, $this->pageName );
			}
		}

		if ( $makeSureIsValid ) {
			$this->getNextAvailablePageName();
		}
	}

	/**
	 * @return array
	 */
	protected function getPageNameVars() {
		$matches = [];
		$vars = [];
		preg_match_all( '/{{(.*?)}}/', $this->pageName, $matches );
		foreach ( $matches[0] as $idx => $var ) {
			$vars[$var] = $matches[1][$idx];
		}

		return $vars;
	}

	/**
	 * @param int $increment
	 * @return string
	 */
	protected function getNextAvailablePageName( $increment = 0 ) {
		$pageName = $this->pageName;
		if ( $increment ) {
			$pageName = "$pageName $increment";
		}
		$title = $this->getTitleFromPageName( $pageName );
		if ( $title instanceof Title && $title->exists() ) {
			$increment++;
			return $this->getNextAvailablePageName( $increment );
		}

		$this->pageName = $pageName;
		return $this->pageName;
	}

	protected function addFormDataToPage() {
		$this->data['_form'] = $this->form;
		if ( $this->exists === false ) {
			$this->tryUpdateFormRevision();
		}
	}

	protected function addPageNameToData() {
		$this->data['_id'] = $this->getTitleFromPageName()->getPrefixedDBkey();
	}

	/**
	 * @return Status
	 */
	protected function saveToPage() {
		$title = $this->getTitleFromPageName();
		try {
			$wikipage = $this->services->getWikiPageFactory()->newFromTitle( $title );
			$content = ContentHandler::makeContent(
				$this->getDataForContent(),
				$title
			);
			$user = RequestContext::getMain()->getUser();
			$updater = $wikipage->newPageUpdater( $user );
			$updater->setContent( SlotRecord::MAIN, $content );
			$summary = $this->summary ?: wfMessage( 'forms-target-json-page-summary', $this->form )->plain();
			$comment = CommentStoreComment::newUnsavedComment( $summary );
			$updater->saveRevision( $comment );
			$saveStatus = $updater->getStatus();
			if ( $saveStatus->isOK() ) {
				return Status::newGood( [
					'title' => $title->getPrefixedDBkey(),
					'id' => $title->getArticleID(),
					'fullURL' => $title->getFullURL()
				] );
			}
			return $saveStatus;
		} catch ( Exception $ex ) {
			return Status::newFatal( $ex->getMessage() );
		}
	}

	/**
	 * @param string $pageName
	 * @return Title
	 */
	protected function getTitleFromPageName( $pageName = '' ) {
		if ( empty( $pageName ) ) {
			$pageName = $this->pageName;
		}
		return Title::newFromText( $pageName . $this->getPageFormat() );
	}

	protected function stripExtension() {
		$extensionLen = strlen( $this->getPageFormat() );
		if ( substr( $this->pageName, -$extensionLen ) === $this->getPageFormat() ) {
			$this->pageName = substr( $this->pageName, 0, -$extensionLen );
		}
	}

	/**
	 * @param string $action
	 * @return bool
	 */
	protected function checkPermissions( $action = 'edit' ) {
		$title = $this->getTitleFromPageName();
		if ( $title instanceof Title === false ) {
			return false;
		}

		$user = RequestContext::getMain()->getUser();
		$userCan = MediaWikiServices::getInstance()->getPermissionManager()
			->userCan( $action, $user, $title );
		if ( $userCan ) {
			return true;
		}
		return false;
	}

	/**
	 * @return string
	 */
	abstract protected function getPageFormat();

	/**
	 * @return string
	 */
	abstract protected function getDataForContent();
}
