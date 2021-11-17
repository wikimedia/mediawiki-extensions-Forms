<?php

namespace MediaWiki\Extension\Forms;

use MediaWiki\Extension\Forms\Tag\CreateForm;
use MediaWiki\Extension\Forms\Tag\FormList;
use MediaWiki\Extension\Forms\Tag\FormMeta;
use Parser;
use ResourceLoader;
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
	 * Register QUnit Tests with MediaWiki framework
	 * @param array &$testModules
	 * @param ResourceLoader &$resourceLoader
	 * @return bool
	 */
	public static function onResourceLoaderTestModules( array &$testModules, ResourceLoader &$resourceLoader ) {
		$testModules['qunit']['ext.forms.tests'] = [
			'scripts' => [
				'tests/qunit/ext.forms.PageFormConverter.test'
			],
			'localBasePath' => dirname( __DIR__ ),
			'remoteExtPath' => 'Forms',
		];

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
