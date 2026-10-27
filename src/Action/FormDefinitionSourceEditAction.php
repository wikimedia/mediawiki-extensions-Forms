<?php

namespace MediaWiki\Extension\Forms\Action;

use MediaWiki\Actions\EditAction;

class FormDefinitionSourceEditAction extends EditAction {
	/**
	 * @return string
	 */
	public function getName() {
		return 'editdefinitionsource';
	}
}
