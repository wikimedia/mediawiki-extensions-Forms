<?php

namespace MediaWiki\Extension\Forms\Hook\LoadExtensionSchemaUpdates;

use DatabaseUpdater;

class AddFormAutosaveTable {
	/**
	 * @param DatabaseUpdater $updater
	 * @return bool
	 */
	public static function callback( $updater ) {
		$path = dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/db';

		$updater->addExtensionTable(
			'form_autosave',
			"$path/form_autosave.sql"
		);

		return true;
	}

}
