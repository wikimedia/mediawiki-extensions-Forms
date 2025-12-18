<?php

namespace MediaWiki\Extension\Forms\Tests\Target;

use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\Extension\Forms\Target\JsonOnWikiPage;
use MediaWiki\Extension\Forms\Target\Template;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Storage\PageUpdater;
use MediaWiki\Title\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\Forms\Target\TitleTarget
 * @covers \MediaWiki\Extension\Forms\Target\JsonOnWikiPage
 * @covers \MediaWiki\Extension\Forms\Target\Template
 */
class TitleTargetTest extends TestCase {

	/**
	 * @dataProvider provideData
	 * @covers       \MediaWiki\Extension\Forms\Target\TitleTarget::execute
	 * @covers       \MediaWiki\Extension\Forms\Target\TitleTarget::saveToPage
	 *
	 * @param string $targetClass
	 * @param array $targetParams
	 * @param array $formParams
	 * @param Title|null $expectedTitle
	 * @param bool $expectException
	 * @return void
	 */
	public function testTargets(
		string $targetClass, array $targetParams, array $formParams,
		?Title $expectedTitle, bool $expectException = false
	) {
		$updater = $this->createMock( PageUpdater::class );
		$wikiPage = $this->createMock( \WikiPage::class );
		$wikiPage->method( 'newPageUpdater' )->willReturn( $updater );

		$wikiPageFactory = $this->createMock( WikiPageFactory::class );
		if ( !$expectException ) {
			$wikiPageFactory
				->expects( $this->once() )
				->method( 'newFromTitle' )
				->with( $expectedTitle )
				->willReturn( $wikiPage );
		} else {
			$this->expectException( \RuntimeException::class );
		}

		$services = $this->createMock( \MediaWiki\MediaWikiServices::class );
		$services->method( 'getWikiPageFactory' )->willReturn( $wikiPageFactory );

		$pm = $this->createMock( PermissionManager::class );
		$pm->method( 'userCan' )->willReturn( true );
		$services->method( 'getPermissionManager' )->willReturn( $pm );

		$formDefManager = $this->createMock( DefinitionManager::class );
		$services->method( 'getService' )
			->with( 'FormsDefinitionManager' )
			->willReturn( $formDefManager );

		$titleFactory = $this->createMock( \MediaWiki\Title\TitleFactory::class );
		$titleFactory->method( 'newFromText' )->willReturnCallback( function ( $text ) {
			if ( str_contains( $text, '{{' ) ) {
				return null;
			}
			return $this->getTitleMock( $text );
		} );
		$services->method( 'getTitleFactory' )->willReturn( $titleFactory );

		$target = new $targetClass(
			$targetParams['form'],
			$targetParams['title'] ? $this->getTitleMock( $targetParams['title'] ) : null,
			$services,
			$targetParams['predefined_title'] ?? ''
		);
		if ( $target instanceof Template ) {
			$target->setTemplate( Title::newFromText( 'Template:TestForm' ) );
		}

		$target->execute( $formParams, '' );
	}

	/**
	 * @return array[]
	 */
	protected function provideData(): array {
		return [
			'json-target-given-title' => [
				'target-class' => JsonOnWikiPage::class,
				'target-params' => [
					'form' => 'TestForm',
					'title' => 'TestPage.formdata',
				],
				'form-data' => [],
				'expected-title' => $this->getTitleMock( 'TestPage.formdata' ),
			],
			'json-target-predefined-title' => [
				'target-class' => JsonOnWikiPage::class,
				'target-params' => [
					'form' => 'TestForm',
					'title' => '',
					'predefined_title' => 'Dummy {{foo}} {{bar}} TestPage'
				],
				'form-data' => [
					'foo' => 'Value1',
					'bar' => 'Value2',
				],
				'expected-title' => $this->getTitleMock( 'Dummy Value1 Value2 TestPage.formdata' ),
			],
			'json-target-predefined-and-given-title' => [
				'target-class' => JsonOnWikiPage::class,
				'target-params' => [
					'form' => 'TestForm',
					'title' => 'TestPage.formdata',
					'predefined_title' => 'Dummy {{foo}} {{bar}} TestPage'
				],
				'form-data' => [
					'foo' => 'Value1',
					'bar' => 'Value2',
				],
				'expected-title' => $this->getTitleMock( 'TestPage.formdata' ),
			],
			'json-target-predefined-title-missing-var' => [
				'target-class' => JsonOnWikiPage::class,
				'target-params' => [
					'form' => 'TestForm',
					'title' => '',
					'predefined_title' => 'Dummy {{foo}} {{bar}} TestPage'
				],
				'form-data' => [
					'bar' => 'Value2',
				],
				'expected-title' => null,
				'expect-exception' => true
			],
			'template-target-given-title' => [
				'target-class' => Template::class,
				'target-params' => [
					'form' => 'TestForm',
					'title' => 'TestPage',
				],
				'form-data' => [],
				'expected-title' => $this->getTitleMock( 'TestPage' ),
			],
			'template-predefined-title' => [
				'target-class' => Template::class,
				'target-params' => [
					'form' => 'TestForm',
					'title' => '',
					'predefined_title' => 'Dummy {{foo}} {{bar}} TestPage'
				],
				'form-data' => [
					'foo' => 'Value1',
					'bar' => 'Value2',
				],
				'expected-title' => $this->getTitleMock( 'Dummy Value1 Value2 TestPage' ),
			],
			'template-predefined-and-given-title' => [
				'target-class' => Template::class,
				'target-params' => [
					'form' => 'TestForm',
					'title' => 'TestPage',
					'predefined_title' => 'Dummy {{foo}} {{bar}} TestPage'
				],
				'form-data' => [
					'foo' => 'Value1',
					'bar' => 'Value2',
				],
				'expected-title' => $this->getTitleMock( 'TestPage' ),
			],
			'template-predefined-title-missing-var' => [
				'target-class' => Template::class,
				'target-params' => [
					'form' => 'TestForm',
					'title' => '',
					'predefined_title' => 'Dummy {{foo}} {{bar}} TestPage'
				],
				'form-data' => [
					'bar' => 'Value2',
				],
				'expected-title' => null,
				'expect-exception' => true
			],
		];
	}

	/**
	 * @param mixed $title
	 * @param string $contentModel
	 * @return MockObject
	 */
	private function getTitleMock( string $title, string $contentModel = 'formdata' ): MockObject {
		$mock = $this->createMock( Title::class );
		$mock->method( 'getText' )->willReturn( $title );
		$mock->method( 'getContentModel' )->willReturn( $contentModel );
		return $mock;
	}

}
