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
		if (
			!$config->has( 'form' ) ||
			(
				!$config->has( 'title' ) &&
				!$config->has( 'predefined_title' )
			)
		) {
			return null;
		}

		$title = null;
		if ( $config->has( 'title' ) ) {
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
		}

		$predefinedTitle = $config->has( 'predefined_title' ) ? $config->get( 'predefined_title' ) : '';
		return new static( $config->get( 'form' ), $title, MediaWikiServices::getInstance(), $predefinedTitle );
	}

	/**
	 * @return string
	 */
	protected function getDataForContent() {
		return FormatJson::encode( $this->data );
	}

	/**
	 * @return string
	 */
	protected function getParsedPredefinedName(): string {
		$name = parent::getParsedPredefinedName();
		if ( !str_ends_with( $name, '.formdata' ) ) {
			$name .= '.formdata';
		}
		return $name;
	}
}
