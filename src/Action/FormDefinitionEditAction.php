<?php

namespace MediaWiki\Extension\Forms\Action;

use FormlessAction;

class FormDefinitionEditAction extends FormlessAction {
	public function getName() {
		return 'edit';
	}

	public function show() {
		$this->useTransactionalTimeLimit();
		$editPage = new \EditPage( $this->page );
		$editPage->setContextTitle( $this->getTitle() );
		$editPage->edit();
	}

	public function onView() {
		return null;
	}
}
