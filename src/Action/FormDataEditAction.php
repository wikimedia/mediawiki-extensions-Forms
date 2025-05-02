<?php

namespace MediaWiki\Extension\Forms\Action;

use FormlessAction;
use MediaWiki\MediaWikiServices;

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
		$this->getOutput()->redirect( $this->getEditUrl() );
	}

	/**
	 * @return string
	 */
	private function getEditUrl(): string {
		$sp = MediaWikiServices::getInstance()->getSpecialPageFactory()->getPage( 'EditWithForm' );
		if ( !$sp ) {
			throw new \RuntimeException( 'EditWithForm special page not found' );
		}
		return $sp->getPageTitle( $this->getTitle()->getPrefixedDBkey() )->getLocalURL();
	}

	/**
	 * @return string
	 */
	protected function getCreateRedirect() {
		return $this->getEditUrl();
	}

	/**
	 * @return null
	 */
	public function onView() {
		return null;
	}
}
