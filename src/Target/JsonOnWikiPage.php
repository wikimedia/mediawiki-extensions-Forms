<?php

namespace MediaWiki\Extension\Forms\Target;

use MediaWiki\Json\FormatJson;

class JsonOnWikiPage extends TitleTarget {

	/**
	 * @return string
	 */
	protected function getDataForContent() {
		return FormatJson::encode( $this->data );
	}

	/**
	 * @return string
	 */
	protected function getPageFormat() {
		return ".formdata";
	}
}
