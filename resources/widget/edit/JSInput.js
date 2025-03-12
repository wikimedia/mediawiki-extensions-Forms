( function ( mw ) {
	mw.ext.forms.widget.edit.JSInput = function ( cfg ) {
		mw.ext.forms.widget.edit.JSInput.parent.call( this, cfg );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.JSInput, OO.ui.MultilineTextInputWidget );

	mw.ext.forms.widget.edit.JSInput.prototype.setValue = function ( value ) {
		if ( value && value.startsWith( 'jscb:' ) ) {
			value = value.split( 'jscb:' ).pop();
		}

		mw.ext.forms.widget.edit.JSInput.parent.prototype.setValue.call( this, value );
	};

	mw.ext.forms.widget.edit.JSInput.prototype.getValue = function () {
		const value = mw.ext.forms.widget.edit.JSInput.parent.prototype.getValue.call( this );
		return 'jscb:' + value;
	};

}( mediaWiki ) );
