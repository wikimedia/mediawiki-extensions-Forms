<?php

namespace MediaWiki\Extension\Forms\Target;

class JsonOnWikiPage extends TitleTarget {

	protected function getDataForContent() {
		return \FormatJson::encode( $this->data );
	}

	protected function getPageFormat() {
		return ".formdata";
	}
}
