<?php

namespace MediaWiki\Extension\Forms;

use MediaWiki\Extension\Forms\Tag\CreateForm;
use MediaWiki\Extension\Forms\Tag\FormList;
use MediaWiki\Extension\Forms\Tag\FormMeta;
use Parser;
use Title;

class Setup {

	/**
	 *
	 * @param Title $title
	 * @param string &$model
	 * @return bool
	 */
	public static function onContentHandlerDefaultModelFor( Title $title, &$model ) {
		if ( preg_match( '/\.form$/', $title->getText() ) && !$title->isTalkPage() ) {
			$model = 'FormDefinition';
			return false;
		}

		if ( preg_match( '/\.formdata$/', $title->getText() ) && !$title->isTalkPage() ) {
			$model = 'FormData';
			return false;
		}
		return true;
	}

	/**
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setHook( 'createForm', [ CreateForm::class, 'callback' ] );
		$parser->setHook( 'formMeta', [ FormMeta::class, 'callback' ] );
		$parser->setHook( 'formList', [ FormList::class, 'callback' ] );
	}
}
