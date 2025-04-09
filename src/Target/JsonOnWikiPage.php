<?php

namespace MediaWiki\Extension\Forms\Target;

use MediaWiki\Config\HashConfig;
use MediaWiki\Extension\Forms\ITarget;
use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;

class JsonOnWikiPage extends TitleTarget {

	/**
	 * @param HashConfig $config
	 * @return ITarget
	 */
	public static function factory( HashConfig $config ) {
		if ( !$config->has( 'form' ) || !$config->has( 'title' ) ) {
			return null;
		}
		$pageName = $config->get( 'title' );
		if ( !str_ends_with( $pageName, '.formdata' ) ) {
			$pageName .= '.formdata';
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
	 * @return string
	 */
	protected function getDataForContent() {
		return FormatJson::encode( $this->data );
	}
}
