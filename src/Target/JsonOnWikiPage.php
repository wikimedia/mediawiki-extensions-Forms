<?php

namespace MediaWiki\Extension\Forms\Target;

class JsonOnWikiPage extends TitleTarget {

	/**
	 * @return string
	 */
	protected function getDataForContent() {
		return \FormatJson::encode( $this->data );
	}

	/**
	 * @return string
	 */
	protected function getPageFormat() {
		return ".formdata";
	}
}
