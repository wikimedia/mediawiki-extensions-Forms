<?php

namespace MediaWiki\Extension\Forms\ContentHandler;

use MediaWiki\Extension\Forms\Action\FormDataEditAction;
use MediaWiki\Extension\Forms\Content\FormDataContent;

class FormDataHandler extends \JsonContentHandler {
	public function __construct( $modelId = 'FormData' ) {
		parent::__construct( $modelId );
	}
	protected function getContentClass() {
		return FormDataContent::class;
	}
	public function supportsSections() {
		return false;
	}
	public function supportsCategories() {
		return true;
	}
	public function supportsRedirects() {
		return false;
	}

	public function getActionOverrides() {
		return [
			'edit' => FormDataEditAction::class
		];
	}

	public function makeRedirectContent( \Title $destination, $text = '' ) {
		$class = $this->getContentClass();
		$json = [
			"_redirect" => $destination->getPrefixedDBkey()
		];
		return new $class( \FormatJson::encode( $json ) );
	}
}
