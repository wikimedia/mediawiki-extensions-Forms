<?php

namespace MediaWiki\Extension\Forms;

class Setup {
	public static function callback() {
		\mwsInitComponents();
		define( 'FORM_DATA_REVISION_SLOT', 'form-data-slot' );
		define( 'FORM_DATA_CONTENT_MODEL', 'FormData' );
		define( 'FORM_DEFINITION_CONTENT_MODEL', 'FormDefinition' );
	}
}
