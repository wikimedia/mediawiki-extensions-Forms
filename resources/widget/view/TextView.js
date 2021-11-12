( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.TextView = function( cfg ) {
		mw.ext.forms.widget.view.TextView.parent.call( this, cfg );
	};

	OO.inheritClass( mw.ext.forms.widget.view.TextView, OO.ui.LabelWidget );

	mw.ext.forms.widget.view.TextView.prototype.setValue = function( value ) {
		this.setLabel( value );
	};

} )( mediaWiki, jQuery );