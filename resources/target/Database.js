( function ( mw ) {
	mw.ext.forms.target.Database = function ( form, standalone ) {
		mw.ext.forms.target.Database.parent.call( this, form, standalone );
	};

	OO.inheritClass( mw.ext.forms.target.Database, mw.ext.forms.target.FormTarget );

	mw.ext.forms.target.Database.prototype.getName = function () {
		return 'database';
	};

	mw.ext.forms.target.Database.prototype.getDisplayName = function () {
		return mw.message( 'forms-form-editor-prop-targettypedatabase' ).text();
	};

	mw.ext.forms.target.Database.prototype.getValue = function () {
		return {
			type: this.getName()
		};
	};

	// Disabled, do we need it?
	// mw.ext.forms.registry.Target.register( 'database', mw.ext.forms.target.Database );
}( mediaWiki ) );
