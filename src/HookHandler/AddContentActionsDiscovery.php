<?php

namespace MediaWiki\Extension\Forms\HookHandler;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;
use BlueSpice\Discovery\ITemplateDataProvider;

class AddContentActionsDiscovery implements BlueSpiceDiscoveryTemplateDataProviderAfterInit {

	/**
	 * @param ITemplateDataProvider $registry
	 * @return void
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		$registry->register( 'panel/edit', 'ca-editdefinitionsource' );
		$registry->register( 'panel/edit', 'ca-edit-with-form' );
	}
}
