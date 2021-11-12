( function ( mw, $, undefined ) {
	mw.ext.forms.widget.edit.Radio = function( cfg ) {
		mw.ext.forms.widget.edit.Radio.parent.call( this, cfg );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.Radio, OO.ui.RadioInputWidget );

	mw.ext.forms.widget.edit.Radio.prototype.setValue = function( value ) {
		this.setSelected( value );
	};

	mw.ext.forms.widget.edit.Radio.prototype.getValue = function() {
		return this.isSelected();
	};

} )( mediaWiki, jQuery );