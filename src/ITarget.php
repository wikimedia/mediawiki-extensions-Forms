<?php

namespace MediaWiki\Extension\Forms;

interface ITarget {

	/**
	 * @param array $formsubmittedData
	 * @param string|null $summary
	 */
	public function execute( $formsubmittedData, $summary );

	/**
	 * Action that occurs after form instance is saved
	 *
	 * Can be overridden by specifying target.afterAction in form definition
	 *
	 * Example - redirecting to a page:
	 * return [
	 *	"type" => "redirect",
	 *	"url" => Title::newMainPage()->getLocalUrl()
	 * ]
	 *
	 * @return array|false
	 */
	public function getDefaultAfterAction();
}
