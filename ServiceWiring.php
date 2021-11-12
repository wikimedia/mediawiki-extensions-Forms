<?php

use MediaWiki\Extension\Forms\Autosaver;
use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\Extension\Forms\FormRevisionManager;
use MediaWiki\MediaWikiServices;

return [

	'FormsDefinitionManager' => function ( MediaWikiServices $services ) {
		return new DefinitionManager();
	},

	'FormsRevisionManager' => function( MediaWikiServices $services ) {
		$revisionStore = $services->getRevisionStore();
		$db = $services->getDBLoadBalancer()->getConnection(
			DB_MASTER
		);
		return FormRevisionManager::factory(
			$revisionStore,
			$db
		);
	},

	'FormsAutosaver' => function( MediaWikiServices $services ) {
		$db = $services->getDBLoadBalancer()->getConnection(
			DB_MASTER
		);
		return Autosaver::factory(
			$db
		);
	}
	
];
