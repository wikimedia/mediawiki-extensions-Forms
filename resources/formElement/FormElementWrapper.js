( function ( mw ) {
	mw.ext.forms.formElement.FormElementWrapper = function () {};

	OO.inheritClass( mw.ext.forms.formElement.FormElementWrapper, mw.ext.forms.formElement.FormElement );

	mw.ext.forms.formElement.FormElementWrapper.prototype.getElementConfig = function () {
		return [];
	};

	mw.ext.forms.formElement.FormElementWrapper.prototype.getType = function () {
		return 'form_element_wrapper';
	};

	mw.ext.forms.formElement.FormElementWrapper.prototype.getWidgets = function () {
		return mw.ext.forms.widget.formElement.FormElementWrapper;
	};

	mw.ext.forms.formElement.FormElementWrapper.prototype.isSystemElement = function () {
		return true;
	};

	mw.ext.forms.registry.Type.register( 'form_element_wrapper', new mw.ext.forms.formElement.FormElementWrapper() );

}( mediaWiki ) );
