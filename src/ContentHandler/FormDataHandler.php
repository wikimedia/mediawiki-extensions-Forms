<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use MediaWiki\Extension\Forms\Action\FormDataEditAction;
use MediaWiki\Extension\Forms\Content\FormDataContent;

class FormDataHandler extends \JsonContentHandler {
	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = 'FormData' ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return FormDataContent::class;
	}

	/**
	 * @return bool
	 */
	public function supportsSections() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function supportsCategories() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function supportsRedirects() {
		return false;
	}

	/**
	 * @return array
	 */
	public function getActionOverrides() {
		return [
			'edit' => FormDataEditAction::class
		];
	}

	/**
	 * @param \Title $destination
	 * @param string $text
	 * @return \MediaWiki\Extension\Forms\ContentHandler\class
	 */
	public function makeRedirectContent( \Title $destination, $text = '' ) {
		$class = $this->getContentClass();
		$json = [
			"_redirect" => $destination->getPrefixedDBkey()
		];
		return new $class( \FormatJson::encode( $json ) );
	}
}
