( function ( mw, $, undefined ) {
	mw.ext.forms.target.JsonOnWikiPage = function( form, items ) {
		mw.ext.forms.target.JsonOnWikiPage.parent.call( this, form, items );
	};

	OO.inheritClass( mw.ext.forms.target.JsonOnWikiPage, mw.ext.forms.target.FormTarget );

	mw.ext.forms.target.JsonOnWikiPage.prototype.getName = function() {
		return 'json-on-wikipage';
	};

	mw.ext.forms.target.JsonOnWikiPage.prototype.getDisplayName = function() {
		return mw.message( 'forms-form-editor-prop-targetTypeJsonOnWikipage' ).text();
	};

	mw.ext.forms.target.JsonOnWikiPage.prototype.getAdditionalFields = function() {
		return [ {
			type: 'text',
			name: 'target.title',
			required: true,
			label: mw.message( 'forms-form-editor-prop-targetTitle' ).text()
		} ];
 	};

	mw.ext.forms.target.JsonOnWikiPage.prototype.getValue = function() {
		return {
			type: this.getName(),
			title: this.items['target.title'].getValue()
		}
	};

	mw.ext.forms.target.JsonOnWikiPage.prototype.setValue = function( value ) {
		if ( value.hasOwnProperty( 'title' ) && this.items.hasOwnProperty( 'target.title' ) ) {
			this.items['target.title'].setValue( value.title );
		}
	};

	mw.ext.forms.registry.Target.register( 'json-on-wikipage', mw.ext.forms.target.JsonOnWikiPage );
} )( mediaWiki, jQuery );
