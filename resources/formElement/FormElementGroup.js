( function ( mw ) {
	mw.ext.forms.formElement.FormElementGroup = function () {};

	OO.inheritClass( mw.ext.forms.formElement.FormElementGroup, mw.ext.forms.formElement.InputFormElement );

	mw.ext.forms.formElement.FormElementGroup.prototype.getElementConfig = function () {
		return {};
	};

	mw.ext.forms.formElement.FormElementGroup.prototype.getType = function () {
		return 'form_element_group';
	};

	mw.ext.forms.formElement.FormElementGroup.prototype.getWidgets = function () {
		return mw.ext.forms.widget.formElement.FormElementGroup;
	};

	mw.ext.forms.formElement.FormElementGroup.prototype.isSystemElement = function () {
		return true;
	};

	mw.ext.forms.registry.Type.register( 'form_element_group', new mw.ext.forms.formElement.FormElementGroup() );
}( mediaWiki ) );
