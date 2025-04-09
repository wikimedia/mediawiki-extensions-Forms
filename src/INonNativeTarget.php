<?php

namespace MediaWiki\Extension\Forms;

use MediaWiki\Content\Content;

interface INonNativeTarget extends ITarget {

	/**
	 * @param Content $content
	 * @return array
	 */
	public function getFormDataFromContent( Content $content ): array;
}
