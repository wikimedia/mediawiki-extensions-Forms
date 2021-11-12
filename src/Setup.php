<?php

namespace MediaWiki\Extension\Forms;

use MediaWiki\Extension\Forms\Tag\CreateForm;
use Title;
use ResourceLoader;

class Setup {

	/**
	 *
	 * @param Title $title
	 * @param string $model
	 * @return boolean
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
	 * @param array $testModules
	 * @param ResourceLoader $resourceLoader
	 * @return boolean
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

	public static function onParserFirstCallInit( \Parser $parser ) {
		$parser->setHook( 'createForm', [ CreateForm::class, 'callback' ] );
	}
}
