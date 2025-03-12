( function ( mw ) {
	mw.ext.forms.formElement.StyleTextarea = function () {};

	OO.inheritClass( mw.ext.forms.formElement.StyleTextarea, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.StyleTextarea.prototype.getType = function () {
		return 'style-textarea';
	};

	mw.ext.forms.formElement.StyleTextarea.prototype.isSystemElement = function () {
		return true;
	};

	mw.ext.forms.formElement.StyleTextarea.prototype.getWidgets = function () {
		return mw.ext.forms.widget.edit.StyleTextarea;
	};

	mw.ext.forms.registry.Type.register( 'style-textarea', new mw.ext.forms.formElement.StyleTextarea() );
}( mediaWiki ) );
