<?php

namespace MediaWiki\Extension\Forms\Action;

use FormlessAction;
use MediaWiki\Content\Content;
use MediaWiki\Extension\Forms\Content\FormDataContent;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\SpecialPage\SpecialPage;

class FormDataEditAction extends FormlessAction {

	/**
	 * @var string
	 */
	protected $contentModel = 'FormData';

	/**
	 * @var string
	 */
	protected $action = 'edit';

	/**
	 * @return string
	 */
	public function getName() {
		return 'edit';
	}

	/**
	 * @return string
	 */
	public function getRestriction() {
		return 'edit';
	}

	public function show() {
		$this->useTransactionalTimeLimit();
		$out = $this->getOutput();

		$this->checkCanExecute( $this->getUser() );

		$content = $this->getCurrentContent();
		if ( $content instanceof FormDataContent === false ) {
			return;
		}

		if ( $this->checkSealed( $content ) ) {
			$out->addReturnTo( $this->getTitle() );
			$out->addWikiMsg( 'forms-edit-error-form-sealed' );
			return;
		}
		if ( $this->action === 'create' ) {
			// All creations in this content model should be done over the SP
			$out->redirect( $this->getCreateRedirect() );
			return;
		}

		$out->setPageTitle( $this->getDisplayTitle() );

		$out->addHTML( $content->getFormContainer( $this->action, $this->getTitle() ) );
		$out->addModules( 'ext.forms.init' );
	}

	/**
	 * @return Content|null
	 */
	protected function getCurrentContent() {
		$rev = $this->getWikipage()->getRevisionRecord();
		$content = $rev ? $rev->getContent( SlotRecord::MAIN ) : null;

		if ( $content === false || $content === null ) {
			$handler = MediaWikiServices::getInstance()->getContentHandlerFactory();
			$handler = $handler->getContentHandler( $this->contentModel );
			$this->action = 'create';
			return $handler->makeEmptyContent();
		}
		return $content;
	}

	/**
	 * @return string
	 * @throws \MWException
	 */
	protected function getCreateRedirect() {
		return SpecialPage::getTitleFor( 'CreateFormInstance' )->getLocalURL();
	}

	/**
	 * @return null
	 */
	public function onView() {
		return null;
	}

	/**
	 * @return string
	 */
	private function getDisplayTitle() {
		$displayTitle = wfMessage(
			"forms-form-edit-title",
			$this->getCurrentContent()->getTitleWithoutExtension( $this->getTitle() )
		)->plain();
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$hookContainer->run( 'FormsGetDisplayTitle', [ $this->getTitle(), &$displayTitle, 'edit' ] );
		return $displayTitle;
	}

	/**
	 * @param FormDataContent $content
	 * @return bool
	 */
	private function checkSealed( FormDataContent $content ) {
		$data = $content->getData()->getValue();
		if ( $data === null ) {
			return false;
		}
		if ( !property_exists( $data, '_sealed' ) ) {
			return false;
		}
		if ( $data->_sealed === true ) {
			return true;
		}
		return false;
	}
}
