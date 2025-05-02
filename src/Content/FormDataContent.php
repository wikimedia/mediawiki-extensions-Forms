<?php

namespace MediaWiki\Extension\Forms\Content;

use MediaWiki\Content\JsonContent;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class FormDataContent extends JsonContent {

	/**
	 * @var string
	 */
	protected $formName;

	/**
	 * @param string $text
	 * @param string $modelId
	 */
	public function __construct( $text, $modelId = "FormData" ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * @param Title $title
	 *
	 * @return string
	 */
	public function getTitleWithoutExtension( $title ) {
		$prefixedText = $title->getPrefixedText();
		return substr( $prefixedText, 0, -strlen( $this->getPageFormat() ) );
	}

	/**
	 * @param Title $title
	 *
	 * @return string
	 */
	public function getDisplayTitle( $title ) {
		$displayTitle = substr( $title->getPrefixedText(), 0, -strlen( $this->getPageFormat() ) );
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$hookContainer->run( 'FormsGetDisplayTitle', [ $title, &$displayTitle, 'view' ] );
		return $displayTitle;
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return $this->getText() === '' || parent::isValid();
	}

	/**
	 * @return string
	 */
	protected function getPageFormat() {
		return ".formdata";
	}
}
