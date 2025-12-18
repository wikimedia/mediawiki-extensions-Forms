<?php

namespace MediaWiki\Extension\Forms\Target;

use Exception;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Config\HashConfig;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Forms\ITarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Storage\PageUpdater;
use MediaWiki\Title\Title;

abstract class TitleTarget implements ITarget {
	/** @var array */
	protected $reservedFields = [ '_form', '_form_rev' ];

	/** @var Title */
	protected $targetTitle;

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

	/** @var string */
	protected $predefinedTitle = '';

	/**
	 * @param string $form
	 * @param Title|null $title
	 * @param MediaWikiServices|null $services
	 * @param string $predefinedTitle
	 */
	public function __construct(
		string $form, ?Title $title, ?MediaWikiServices $services, string $predefinedTitle = ''
	) {
		$this->form = $form;
		$this->targetTitle = $title;
		$this->predefinedTitle = $predefinedTitle;
		$this->services = $services ?: MediaWikiServices::getInstance();
	}

	/**
	 * @param HashConfig $config
	 * @return ITarget
	 */
	public static function factory( HashConfig $config ) {
		if (
			!$config->has( 'form' ) ||
			(
				!$config->has( 'title' ) &&
				!$config->has( 'predefined_title' )
			)
		) {
			return null;
		}
		$title = null;
		if ( $config->has( 'title' ) ) {
			$title = MediaWikiServices::getInstance()->getTitleFactory()->newFromText( $config->get( 'title' ) );
			if ( !$title ) {
				throw new \RuntimeException(
					'Invalid title ' . $config->get( 'title' ) . ' for form target'
				);
			}
		}

		$predefinedTitle = $config->has( 'predefined_title' ) ? $config->get( 'predefined_title' ) : '';
		return new static( $config->get( 'form' ), $title, MediaWikiServices::getInstance(), $predefinedTitle );
	}

	/**
	 * @param array $formsubmittedData
	 * @param string $summary
	 * @return Status
	 */
	public function execute( $formsubmittedData, $summary ) {
		$this->data = $formsubmittedData;
		$this->summary = $summary;

		if ( !$this->targetTitle && $this->predefinedTitle ) {
			$this->targetTitle = $this->getTitleFromPredefined();
		}

		if ( !$this->checkPermissions() ) {
			return Status::newFatal( 'badaccess-group0' );
		}
		$this->addFormDataToPage();
		return $this->saveToPage();
	}

	/**
	 * @return array
	 */
	public function getDefaultAfterAction() {
		return [
			"type" => "redirect",
			"url" => $this->targetTitle->getFullURL()
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
	 * @return string
	 */
	public function getPredefinedTitle(): string {
		return $this->predefinedTitle;
	}

	/**
	 * @return void
	 */
	protected function addFormDataToPage() {
		$this->data['_form'] = $this->form;
		if ( $this->exists === false ) {
			$this->tryUpdateFormRevision();
		}
	}

	/**
	 * @return Status
	 */
	protected function saveToPage() {
		try {
			$wikipage = $this->services->getWikiPageFactory()->newFromTitle( $this->targetTitle );
			$content = ContentHandler::makeContent(
				$this->getDataForContent(),
				$this->targetTitle
			);
			$user = RequestContext::getMain()->getUser();
			$updater = $wikipage->newPageUpdater( $user );
			$updater->setContent( SlotRecord::MAIN, $content );
			$this->setUpdaterContent( $updater );
			$summary = $this->summary ?: wfMessage( 'forms-target-json-page-summary', $this->form )->plain();
			$comment = CommentStoreComment::newUnsavedComment( $summary );
			$updater->saveRevision( $comment );
			$saveStatus = $updater->getStatus();
			if ( $saveStatus->isOK() ) {
				return Status::newGood( [
					'title' => $this->targetTitle->getPrefixedDBkey(),
					'id' => $this->targetTitle->getArticleID(),
					'fullURL' => $this->targetTitle->getFullURL()
				] );
			}
			return $saveStatus;
		} catch ( Exception $ex ) {
			return Status::newFatal( $ex->getMessage() );
		}
	}

	protected function setUpdaterContent( PageUpdater $updater ) {
		// Do nothing by default
	}

	/**
	 * @param string $action
	 * @return bool
	 */
	protected function checkPermissions( $action = 'edit' ) {
		$title = $this->targetTitle;
		$user = RequestContext::getMain()->getUser();
		return $this->services->getPermissionManager()->userCan( $action, $user, $title );
	}

	/**
	 * @return string
	 */
	abstract protected function getDataForContent();

	/**
	 * @return Title
	 */
	private function getTitleFromPredefined(): Title {
		$name = $this->getParsedPredefinedName();
		$title = $this->services->getTitleFactory()->newFromText( $name );
		if ( !$title ) {
			throw new \RuntimeException(
				Message::newFromKey( 'forms-target-predefined-title-error' )->text()
			);
		}
		if ( $title->exists() ) {
			throw new \RuntimeException(
				Message::newFromKey( 'forms-target-predefined-title-exists-error' )->text()
			);
		}
		return $title;
	}

	/**
	 * @return string
	 */
	protected function getParsedPredefinedName(): string {
		$vars = $this->getPageNameVars();
		$pagename = $this->predefinedTitle;
		foreach ( $vars as $placeholder => $var ) {
			$replacement = $this->data[$var] ?? '';
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
				$pagename = preg_replace( "/$placeholder/", $replacement, $pagename );
			}
		}

		if ( empty( $pagename ) ) {
			throw new \RuntimeException( Message::newFromKey( 'forms-target-predefined-title-error' )->text() );
		}

		return $pagename;
	}

	/**
	 * @return array
	 */
	private function getPageNameVars() {
		$matches = [];
		$vars = [];
		preg_match_all( '/{{(.*?)}}/', $this->predefinedTitle, $matches );
		foreach ( $matches[0] as $idx => $var ) {
			$vars[$var] = $matches[1][$idx];
		}

		return $vars;
	}

}
