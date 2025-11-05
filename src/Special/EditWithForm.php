<?php

namespace MediaWiki\Extension\Forms\Special;

use MediaWiki\Content\JsonContent;
use MediaWiki\Extension\Forms\Content\FormDataContent;
use MediaWiki\Extension\Forms\DefinitionManager;
use MediaWiki\Extension\Forms\INonNativeTarget;
use MediaWiki\Extension\Forms\Util\PickerMaker;
use MediaWiki\Extension\Forms\Util\TargetFactory;
use MediaWiki\Message\Message;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use OOUI\MessageWidget;

class EditWithForm extends FormSpecial {

	/**
	 * @var string
	 */
	protected $formDefinition = '';

	/** @var Title|null */
	private ?Title $targetPage = null;

	/**
	 * @param DefinitionManager $definitionManager
	 * @param TitleFactory $titleFactory
	 * @param RevisionLookup $revisionLookup
	 */
	public function __construct(
		private readonly DefinitionManager $definitionManager,
		private readonly TitleFactory $titleFactory,
		private readonly RevisionLookup $revisionLookup
	) {
		parent::__construct( 'EditWithForm' );
	}

	/**
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		if ( !$subPage ) {
			$subPage = $this->getRequest()->getText( 'target', null );
		}
		$this->setTargetPage( $subPage );
		if ( $this->targetPage ) {
			$this->getOutput()->setPageTitle(
				$this->msg( 'editwithform-with-title', $this->targetPage->getPrefixedText() )->escaped()
			);
		}

		$formData = $this->getFormDataFromTargetPage();
		if ( $formData && $this->isSealed( $formData ) ) {
			$this->outputError( $this->msg( 'forms-edit-error-form-sealed' ) );
			return;
		}
		// If target page exists and its create by a form, use that form
		$formName = $formData['_form'] ?? null;
		if ( !$formName ) {
			if ( $this->targetPage && $this->targetPage->exists() ) {
				$this->outputError( $this->msg( 'forms-page-already-exists-no-form', $this->targetPage->getText() ) );
				return;
			}
			// otherwise check query params
			$formName = $this->getRequest()->getText( 'formName', null );
			if ( !$formName ) {
				// or show the form picker
				$this->outputFormSelector();
				return;
			}
		}
		if ( !$this->definitionManager->definitionExists( $formName ) ) {
			$this->outputError( $this->msg( 'forms-definitions-not-exist', $formName ) );
			return;
		}
		$this->formDefinition = $formName;
		if ( !$this->targetPage ) {
			$this->outputTargetPageForm();
			return;
		}

		$this->insertDependencies();
		$this->getOutput()->addHtml(
			$this->getFormContainer(
				$this->formDefinition, $this->targetPage, $formData ? json_encode( $formData ) : '',
				'create'
			)
		);
	}

	/**
	 * @param string|null $targetPage
	 * @return void|null
	 */
	private function setTargetPage( ?string $targetPage ) {
		if ( !$targetPage ) {
			return null;
		}
		$this->targetPage = $this->titleFactory->newFromText( $targetPage );
	}

	/**
	 * @return void
	 */
	private function outputFormSelector() {
		$this->getOutput()->enableOOUI();
		$this->getOutput()->addHtml(
			( new PickerMaker() )->makeFormDefinitionPicker(
				$this->definitionManager->getDefinitionKeys(),
				$this->targetPage
			)->toString()
		);
	}

	/**
	 * @param Message $msg
	 * @return void
	 */
	private function outputError( Message $msg ) {
		$message = new MessageWidget( [
			'label' => $msg->text(),
			'type' => 'error',
		] );
		$this->getOutput()->enableOOUI();
		$this->getOutput()->addHtml( $message->toString() );
	}

	private function outputTargetPageForm() {
		$this->getOutput()->enableOOUI();
		$this->getOutput()->addHtml(
			( new PickerMaker() )->makeTargetTitlePicker( $this->formDefinition )->toString()
		);
	}

	/**
	 * @return array|null
	 */
	private function getFormDataFromTargetPage(): ?array {
		if ( !$this->targetPage || !$this->targetPage->exists() ) {
			return null;
		}
		$revision = $this->revisionLookup->getRevisionByTitle(
			$this->targetPage->toPageIdentity()
		);
		if ( !$revision ) {
			return null;
		}
		if ( $this->targetPage->getContentModel() === 'FormData' ) {
			$content = $revision->getContent( SlotRecord::MAIN );
			return $this->getDataFromJsonContent( $content );
		}
		if ( $revision->hasSlot( FORM_DATA_REVISION_SLOT ) ) {
			$formData = $revision->getContent( FORM_DATA_REVISION_SLOT );
			$jsonData = $this->getDataFromJsonContent( $formData );
			if ( !$jsonData ) {
				return null;
			}
			$mainSlot = $revision->getContent( SlotRecord::MAIN );
			if ( !$mainSlot ) {
				return [
					'_form' => $jsonData['form'] ?? null,
				];
			}
			$target = ( new TargetFactory() )->makeTarget( $jsonData['_target' ], array_merge( [
				'title' => $this->targetPage->getPrefixedText(),
			], $jsonData ) );
			$mainData = [];
			if ( $target instanceof INonNativeTarget ) {
				$mainData = $target->getFormDataFromContent( $mainSlot );
			}

			return array_merge( $mainData, [
				'_form' => $jsonData['form'] ?? null,
			] );
		}
		return null;
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	private function isSealed( array $data ) {
		return isset( $data['_sealed'] ) && $data['_sealed'] === true;
	}

	/**
	 * @param JsonContent|null $content
	 * @return array|null
	 */
	private function getDataFromJsonContent( ?JsonContent $content ): ?array {
		if ( !( $content instanceof FormDataContent ) ) {
			return null;
		}
		$data = $content->getData();
		if ( !$data->isOK() ) {
			return null;
		}
		return (array)$data->getValue();
	}
}
