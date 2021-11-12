( function ( mw, $, undefined ) {
	mw.ext.forms.widget.edit.Checkbox = function( cfg ) {
		mw.ext.forms.widget.edit.Checkbox.parent.call( this, cfg );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.Checkbox, OO.ui.CheckboxInputWidget );

	mw.ext.forms.widget.edit.Checkbox.prototype.setValue = function( value ) {
		this.setSelected( value );
	};

	mw.ext.forms.widget.edit.Checkbox.prototype.getValue = function() {
		return this.isSelected();
	};

} )( mediaWiki, jQuery );