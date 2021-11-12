<?php

use MediaWiki\MediaWikiServices;

return [

	'FormsDefinitionManager' => function ( MediaWikiServices $services ) {
		return new \MediaWiki\Extension\Forms\DefinitionManager();
	}
];
