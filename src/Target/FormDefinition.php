<?php

namespace MediaWiki\Extension\Forms\Target;

use MediaWiki\Extension\Forms\FormRevisionManager;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use Status;

class FormDefinition extends JsonOnWikiPage {

	/**
	 * @return string
	 */
	protected function getPageFormat() {
		return ".form";
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
			return \Status::newFatal( 'badaccess-group0' );
		}

		$this->addPageNameToData();
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
