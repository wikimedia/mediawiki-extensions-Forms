<?php

use MediaWiki\Extension\Forms\Autosaver;
use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\Extension\Forms\FormRevisionManager;
use MediaWiki\Extension\Forms\Util\DataPreprocessor;
use MediaWiki\MediaWikiServices;

return [

	'FormsDefinitionManager' => static function ( MediaWikiServices $services ) {
		return new DefinitionManager();
	},

	'FormsRevisionManager' => static function ( MediaWikiServices $services ) {
		$revisionStore = $services->getRevisionStore();
		$db = $services->getDBLoadBalancer()->getConnection(
			DB_MASTER
		);
		return FormRevisionManager::factory(
			$revisionStore,
			$db
		);
	},

	'FormsAutosaver' => static function ( MediaWikiServices $services ) {
		$db = $services->getDBLoadBalancer()->getConnection(
			DB_MASTER
		);
		return Autosaver::factory(
			$db
		);
	},

	'FormsDataPreprocessor' => static function ( MediaWikiServices $services ) {
		$context = RequestContext::getMain();
		return new DataPreprocessor( $services->getParser(), $context );
	}

];
