( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.CheckboxMultiselectView = function( cfg ) {
		mw.ext.forms.widget.view.CheckboxMultiselectView.parent.call( this, cfg );
		this.setDisabled( true );
	};

	OO.inheritClass( mw.ext.forms.widget.view.CheckboxMultiselectView, OO.ui.CheckboxMultiselectInputWidget );

} )( mediaWiki, jQuery );