<?php

namespace MediaWiki\Extension\Forms;

use MediaWiki\MediaWikiServices;

class DefinitionManager {
	/**
	 * @var array
	 */
	protected $definitions = [];

	public function __construct() {
		$this->loadDefinitions();
	}

	/**
	 * Checks if given definition exists
	 *
	 * @param string $definition
	 * @return bool
	 */
	public function definitionExists( $definition ) {
		return isset( $this->definitions[$definition] );
	}

	/**
	 * Returns definition by the given key
	 *
	 * @param string $definition
	 * @return string
	 * @throws \MWException
	 */
	public function getDefinition( $definition ) {
		if ( $this->definitionExists( $definition ) ) {
			return $this->definitions[$definition];
		}
		throw new \MWException( 'Definition does not exist' );
	}

	/**
	 * Gets names of all registered definitions
	 * @return array
	 */
	public function getDefinitionKeys() {
		return array_keys( $this->definitions );
	}

	protected function loadDefinitions() {
		$this->loadFromAttribute();
		$this->loadFromContentModel();
	}

	protected function loadFromAttribute() {
		$attribute = \ExtensionRegistry::getInstance()->getAttribute(
			"FormsDefinitions"
		);

		foreach ( $attribute as $key => $definition ) {
			if ( $this->fileExists( $definition ) ) {
				$this->definitions[$key] = $this->getContent(
					$definition
				);
			}
		}
	}

	protected function loadFromContentModel() {
		$pages = $this->getPages();
		foreach( $pages as $pageRow ) {
			$page = \Title::newFromRow( $pageRow );
			$wikipage = \WikiPage::factory( $page );
			$content = $wikipage->getContent();

			$name = $content->getTitleWithoutExtension( $page );
			$data = $content->getNativeData();
			$this->definitions[$name] = $data;

		}
	}

	protected function getPages() {
		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(
			DB_REPLICA
		);
		$res = $db->select(
			'page',
			'*',
			[ 'page_content_model' => 'FormDefinition' ]
		);

		return $res;
	}


	protected function fileExists( $definition ) {
		return file_exists( $this->expandPath( $definition ) );
	}

	protected function getContent( $definition ) {
		return file_get_contents( $this->expandPath( $definition ) );
	}

	protected function expandPath( $definition ) {
		return dirname( dirname( dirname ( __FILE__ ) ) ) . "/$definition";
	}
}
