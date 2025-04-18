<?php

namespace MediaWiki\Extension\Forms\Tag;

use MediaWiki\Message\Message;

class FormButton extends FormTag {

	public function handle() {
		return Message::newFromKey( 'forms-form-meta-tag' )->params(
			$this->args['_form'] . '.form',
			$this->args['_form_rev']
		)->parseAsBlock();
	}
}
