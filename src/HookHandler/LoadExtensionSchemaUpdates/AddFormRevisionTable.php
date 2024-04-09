<?php

namespace MediaWiki\Extension\Forms\HookHandler\LoadExtensionSchemaUpdates;

use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class AddFormRevisionTable implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @inheritDoc
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dbType = $updater->getDB()->getType();
		$dir = dirname( __DIR__, 3 );

		$updater->addExtensionTable(
			'form_revision',
			"$dir/db/$dbType/form_revision.sql"
		);
	}
}
