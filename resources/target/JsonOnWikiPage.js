( function ( mw ) {
	mw.ext.forms.target.JsonOnWikiPage = function ( form, standalone ) {
		mw.ext.forms.target.JsonOnWikiPage.parent.call( this, form, standalone );
	};

	OO.inheritClass( mw.ext.forms.target.JsonOnWikiPage, mw.ext.forms.target.WikipageTarget );

	mw.ext.forms.target.JsonOnWikiPage.prototype.getName = function () {
		return 'json-on-wikipage';
	};

	mw.ext.forms.target.JsonOnWikiPage.prototype.getDisplayName = function () {
		return mw.message( 'forms-form-editor-prop-targettypejsononwikipage' ).text();
	};

	mw.ext.forms.target.JsonOnWikiPage.prototype.getAdditionalFields = function () {
		return [
			{
				type: 'text',
				help: mw.msg( 'forms-form-editor-prop-target-json-wikipage-predefined-title-help' ),
				name: 'target.predefined_page_name',
				required: false,
				label: mw.msg( 'forms-form-editor-prop-target-json-wikipage-predefined-title' ),
				widget_placeholder: mw.msg( 'forms-form-editor-prop-target-json-wikipage-predefined-title-ph' )
			}
		];
	};

	mw.ext.forms.target.JsonOnWikiPage.prototype.getValue = function () {
		return {
			type: this.getName(),
			predefined_title: this.items[ 'target.predefined_page_name' ].getValue()
		};
	};

	mw.ext.forms.target.JsonOnWikiPage.prototype.setValue = function ( value ) {
		if ( value.hasOwnProperty( 'predefined_title' ) && this.items.hasOwnProperty( 'target.predefined_page_name' ) ) {
			this.items[ 'target.predefined_page_name' ].setValue( value.predefined_title );
		}
	};

	mw.ext.forms.registry.Target.register( 'json-on-wikipage', mw.ext.forms.target.JsonOnWikiPage );
}( mediaWiki ) );
