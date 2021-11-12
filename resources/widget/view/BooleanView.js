( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.BooleanView = function( cfg ) {
		mw.ext.forms.widget.view.BooleanView.parent.call( this, cfg );
	};

	OO.inheritClass( mw.ext.forms.widget.view.BooleanView, OO.ui.IconWidget );

	mw.ext.forms.widget.view.BooleanView.prototype.setValue = function( value ) {
		if ( value ) {
			this.setIcon( 'check' );
		} else {
			this.setIcon( 'close' );
		}
	};

} )( mediaWiki, jQuery );