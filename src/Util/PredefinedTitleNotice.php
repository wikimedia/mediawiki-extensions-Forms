<?php

namespace MediaWiki\Extension\Forms\Util;

use MediaWiki\Message\Message;
use OOUI\MessageWidget;

class PredefinedTitleNotice extends MessageWidget {

	/**
	 * @param string $predefinedTitle
	 */
	public function __construct( string $predefinedTitle ) {
		parent::__construct( [
			'type' => 'info',
			'label' => Message::newFromKey( 'forms-form-will-be-created-as' )->plaintextParams(
				$predefinedTitle
			)->text(),
		] );
	}

}
