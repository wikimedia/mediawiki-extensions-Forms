<?php

namespace MediaWiki\Extension\Forms\Hook\LoadExtensionSchemaUpdates;

use DatabaseUpdater;

class AddFormDataTable {
	/**
	 * @param DatabaseUpdater $updater
	 * @return bool
	 */
	public static function callback( $updater ) {
		$path = dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/db';

		$updater->addExtensionTable(
			'form_data',
			"$path/form_data.sql"
		);

		return true;
	}

}
