<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Forms\Autosaver;
use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\Extension\Forms\FormRevisionManager;
use MediaWiki\Extension\Forms\Util\DataPreprocessor;
use MediaWiki\MediaWikiServices;

/** @phpcs-require-sorted-array */
return [

	'FormsAutosaver' => static function ( MediaWikiServices $services ): Autosaver {
		$db = $services->getDBLoadBalancer()->getConnection(
			DB_PRIMARY
		);
		return Autosaver::factory(
			$db
		);
	},

	'FormsDataPreprocessor' => static function ( MediaWikiServices $services ): DataPreprocessor {
		$context = RequestContext::getMain();
		return new DataPreprocessor( $services->getParser(), $context );
	},

	'FormsDefinitionManager' => static function ( MediaWikiServices $services ): DefinitionManager {
		return new DefinitionManager();
	},

	'FormsRevisionManager' => static function ( MediaWikiServices $services ): FormRevisionManager {
		$revisionStore = $services->getRevisionStore();
		$db = $services->getDBLoadBalancer()->getConnection(
			DB_PRIMARY
		);
		return FormRevisionManager::factory(
			$revisionStore,
			$db
		);
	},

];
