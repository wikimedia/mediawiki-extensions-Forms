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

	/**
	 * @param string $form
	 * @param Title $title
	 */
	protected function __construct( string $form, Title $title ) {
		$this->form = $form;
		$this->targetTitle = $title;
		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 * @param HashConfig $config
	 * @return ITarget
	 */
	public static function factory( HashConfig $config ) {
		if ( !$config->has( 'form' ) || !$config->has( 'title' ) ) {
			return null;
		}
		$title = MediaWikiServices::getInstance()->getTitleFactory()->newFromText( $config->get( 'title' ) );
		if ( !$title ) {
			throw new \RuntimeException(
				'Invalid title ' . $config->get( 'title' ) . ' for form target'
			);
		}
		return new static( $config->get( 'form' ), $title );
	}

	/**
	 * @param array $formsubmittedData
	 * @param string $summary
	 * @return Status
	 */
	public function execute( $formsubmittedData, $summary ) {
		$this->data = $formsubmittedData;
		$this->summary = $summary;

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
		return MediaWikiServices::getInstance()->getPermissionManager()->userCan( $action, $user, $title );
	}

	/**
	 * @return string
	 */
	abstract protected function getDataForContent();
}
