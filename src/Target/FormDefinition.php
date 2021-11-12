<?php

namespace MediaWiki\Extension\Forms\Target;

use MediaWiki\Extension\Forms\FormRevisionManager;
use MediaWiki\MediaWikiServices;
use Title;

class FormDefinition extends JsonOnWikiPage {

	protected function getPageFormat() {
		return ".form";
	}

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
