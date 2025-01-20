<?php

namespace MediaWiki\Extension\Forms\Util;

use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Parser;
use ParserOptions;
use RequestContext;

class DataPreprocessor {

	/**
	 *
	 * @var Parser
	 */
	private $parser;

	/**
	 *
	 * @var RequestContext
	 */
	private $requestContext;

	/**
	 *
	 * @param Parser $parser
	 * @param RequestContext|null $requestContext
	 */
	public function __construct( $parser, $requestContext = null ) {
		$this->parser = $parser;
		$this->requestContext = $requestContext;
	}

	/**
	 *
	 * @param array $data
	 * @param string $mailBody
	 * @param User $user
	 * @return string
	 */
	public function preprocess( $data, $mailBody, $user ) {
		$parserOptions = ParserOptions::newFromUser( $user );
		$this->parser->setOptions( $parserOptions );
		$this->parser->clearState();

		$frame = $this->parser->getPreprocessor()->newCustomFrame( $data );

		$preprocessed = $this->parser->preprocess(
			$mailBody,
			Title::newMainPage(),
			$parserOptions,
			null,
			$frame
		);

		return $preprocessed;
	}

}
