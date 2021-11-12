( function ( mw, $, undefined ) {
	mw.ext.forms.formElement.JSInput = function() {};

	OO.inheritClass( mw.ext.forms.formElement.JSInput, mw.ext.forms.formElement.TextArea );

	mw.ext.forms.formElement.JSInput.prototype.getType = function() {
		return "js_input";
	};

	mw.ext.forms.formElement.JSInput.prototype.getWidgets = function() {
		return mw.ext.forms.widget.edit.JSInput;
	};

	mw.ext.forms.formElement.JSInput.prototype.isSystemElement = function() {
		return true;
	};

	mw.ext.forms.registry.Type.register( "js_input", new mw.ext.forms.formElement.JSInput() );
} )( mediaWiki, jQuery );