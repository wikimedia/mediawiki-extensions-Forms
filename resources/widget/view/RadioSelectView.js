( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.RadioSelectView = function( cfg ) {
		mw.ext.forms.widget.view.RadioSelectView.parent.call( this, cfg );
		this.setDisabled( true );
	};

	OO.inheritClass( mw.ext.forms.widget.view.RadioSelectView, OO.ui.RadioSelectInputWidget );

} )( mediaWiki, jQuery );