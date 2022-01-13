<?php

namespace MediaWiki\Extension\Forms\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddEditDefinionSourceAction extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		$currentContentModel = $this->skin->getTitle()->getContentModel();
		if ( in_array( $currentContentModel, [ 'FormDefinition', 'FormData' ] ) ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		$this->template->data[SkinData::FEATURED_ACTIONS]['edit']['editdefinitionsource'] = [
			'position' => '10',
			'id' => 'edit-definition-source',
			'text' => $this->msg( 'forms-action-editsource' ),
			'title' => $this->msg( 'forms-action-editsource' ),
			'href' => $this->skin->getTitle()->getLocalURL( [
				'action' => 'editdefinitionsource',
			] )
		];

		unset( $this->template->data[SkinData::FEATURED_ACTIONS]['edit']['new-section'] );
	}
}
