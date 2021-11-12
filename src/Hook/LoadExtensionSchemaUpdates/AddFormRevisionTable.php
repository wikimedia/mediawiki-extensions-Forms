<?php

namespace MediaWiki\Extension\Forms\Hook\LoadExtensionSchemaUpdates;

use DatabaseUpdater;

class AddFormRevisionTable {
	/**
	 * @param DatabaseUpdater $updater
	 * @return bool
	 */
	public static function callback( $updater ) {
		$path = dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/db';

		$updater->addExtensionTable(
			'form_revision',
			"$path/form_revision.sql"
		);

		return true;
	}

}
