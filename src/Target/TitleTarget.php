<?php

namespace MediaWiki\Extension\Forms\Target;

use HashConfig;
use MediaWiki\Extension\Forms\ITarget;
use MediaWiki\MediaWikiServices;
use MWException;
use Status;
use Title;

abstract class TitleTarget implements ITarget {
	/** @var array  */
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

	protected function __construct( $pageName, $form, $exists ) {
		$this->pageName = $pageName;
		// Make sure operating page name is not suffixed
		$this->stripExtension();
		$this->form = $form;
		$this->exists = $exists;
	}

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
		$definitionManager = MediaWikiServices::getInstance()->getService(
			"FormsDefinitionManager"
		);
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
				$user = \RequestContext::getMain()->getUser();
				if ( $user->isAnon() ) {
					$replacement = '';
				} else {
					$replacement = $user->getName();
				}
			}
			if ( !empty( $replacement ) ) {
				$this->pageName = preg_replace("/$placeholder/", $replacement, $this->pageName);
			}
		}

		if ( $makeSureIsValid ) {
			$this->getNextAvailablePageName();
		}
	}

	protected function getPageNameVars() {
		$matches = [];
		$vars = [];
		preg_match_all( '/{{(.*?)}}/', $this->pageName, $matches );
		foreach( $matches[0] as $idx => $var ) {
			$vars[$var] = $matches[1][$idx];
		}

		return $vars;
	}

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

	protected function saveToPage() {
		$title = $this->getTitleFromPageName();
		try {
			$wikipage = \WikiPage::factory( $title );
			$content = \ContentHandler::makeContent(
				$this->getDataForContent(),
				$title
			);
			$saveStatus = $wikipage->doEditContent(
				$content,
				$this->summary ?: wfMessage( 'forms-target-json-page-summary', $this->form )->plain()
			);
			if ( $saveStatus->isOK() ) {
				return Status::newGood( [
					'title' => $title->getPrefixedDBkey(),
					'id' => $title->getArticleID(),
					'fullURL' => $title->getFullURL()
				] );
			}
			return $saveStatus;
		} catch ( MWException $ex ) {
			return Status::newFatal( $ex->getMessage() );
		}
	}

	protected function getTitleFromPageName( $pageName = '' ) {
		if ( empty( $pageName ) ) {
			$pageName = $this->pageName;
		}
		return Title::newFromText( $pageName . $this->getPageFormat() ) ;
	}

	protected function stripExtension() {
		$extensionLen = strlen( $this->getPageFormat() );
		if ( substr( $this->pageName, -$extensionLen ) === $this->getPageFormat() ) {
			$this->pageName = substr( $this->pageName, 0, -$extensionLen );
		}
	}

	protected function checkPermissions( $action = 'edit' ) {
		$title = $this->getTitleFromPageName();
		if ( $title instanceof Title === false ) {
			return false;
		}

		if ( $title->userCan( $action ) ) {
			return true;
		}
		return false;
	}

	abstract protected function getPageFormat();

	abstract protected function getDataForContent();
}