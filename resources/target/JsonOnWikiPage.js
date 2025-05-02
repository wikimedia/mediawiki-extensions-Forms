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

	mw.ext.forms.target.JsonOnWikiPage.prototype.getValue = function () {
		return {
			type: this.getName()
		};
	};

	mw.ext.forms.registry.Target.register( 'json-on-wikipage', mw.ext.forms.target.JsonOnWikiPage );
}( mediaWiki ) );
