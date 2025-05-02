<?php

namespace MediaWiki\Extension\Forms\Target;

use MediaWiki\Config\HashConfig;
use MediaWiki\Extension\Forms\FormRevisionManager;
use MediaWiki\Extension\Forms\ITarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;

class FormDefinition extends JsonOnWikiPage {

	/**
	 * @param HashConfig $config
	 * @return ITarget
	 */
	public static function factory( HashConfig $config ) {
		if ( !$config->has( 'form' ) || !$config->has( 'title' ) ) {
			return null;
		}
		$pageName = $config->get( 'title' );
		if ( !str_ends_with( $pageName, '.form' ) ) {
			$pageName .= '.form';
		}
		$title = MediaWikiServices::getInstance()->getTitleFactory()->newFromText( $pageName );
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

		$saveStatus = $this->saveToPage();
		if ( $saveStatus->isGood() ) {
			$this->insertRev( $saveStatus->getValue() );
		}
		return $saveStatus;
	}

	/**
	 * @param array $saveValue
	 */
	private function insertRev( $saveValue ) {
		/** @var FormRevisionManager $revManager */
		$revManager = MediaWikiServices::getInstance()->getService(
			'FormsRevisionManager'
		);

		$title = Title::newFromID( $saveValue['id'] );
		if ( !$title instanceof Title ) {
			return;
		}
		if ( isset( $this->data['useFormRevs'] ) && $this->data['useFormRevs'] === false ) {
			// Form specifies not to use form revisions - if any already saved, remove them
			$revManager->deleteForForm( $title->getArticleID() );
			return;
		}

		$revManager->insert(
			$title,
			$title->getLatestRevID(),
			wfTimestamp( TS_MW )
		);
	}
}
