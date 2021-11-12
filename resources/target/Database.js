( function ( mw, $, undefined ) {
	mw.ext.forms.target.Database = function( form, items ) {
		mw.ext.forms.target.Database.parent.call( this, form, items );
	};

	OO.inheritClass( mw.ext.forms.target.Database, mw.ext.forms.target.FormTarget );

	mw.ext.forms.target.Database.prototype.getName = function() {
		return 'database';
	};

	mw.ext.forms.target.Database.prototype.getDisplayName = function() {
		return mw.message( 'forms-form-editor-prop-targetTypeDatabase' ).text();
	};

	mw.ext.forms.target.Database.prototype.getAdditionalFields = function() {
		return [ {
			type: 'text',
			name: 'target.title',
			label: mw.message( 'forms-form-editor-prop-targetTitle' ).text()
		} ];
 	};

	mw.ext.forms.target.Database.prototype.getValue = function() {
		return {
			type: this.getName(),
			title: this.items['target.title'].getValue()
		}
	};

	mw.ext.forms.target.Database.prototype.setValue = function( value ) {
		if ( value.hasOwnProperty( 'title' ) && this.items.hasOwnProperty( 'target.title' ) ) {
			this.items['target.title'].setValue( value.title );
		}
	};

	mw.ext.forms.registry.Target.register( 'database', mw.ext.forms.target.Database );
} )( mediaWiki, jQuery );
