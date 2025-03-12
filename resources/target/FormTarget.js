( function ( mw ) {
	mw.ext.forms.target.FormTarget = function ( form, standalone ) {
		this.form = form;
		this.standalone = standalone;
		this.items = {};
	};

	OO.initClass( mw.ext.forms.target.FormTarget );

	mw.ext.forms.target.FormTarget.prototype.setItems = function ( items ) {
		this.items = items;
		this.init();
	};

	mw.ext.forms.target.FormTarget.prototype.init = function () {
		// STUB
	};

	mw.ext.forms.target.FormTarget.prototype.getName = function () {
		// STUB
		return '';
	};

	mw.ext.forms.target.FormTarget.prototype.getDisplayName = function () {
		// STUB
		return '';
	};

	mw.ext.forms.target.FormTarget.prototype.getAdditionalFields = function () {
		// STUB
		return {};
	};

	mw.ext.forms.target.FormTarget.prototype.getWidgetFields = function ( fields ) {
		// STUB
		return fields;
	};

	mw.ext.forms.target.FormTarget.prototype.getValue = function () {
		// STUB
		return {};
	};

	mw.ext.forms.target.FormTarget.prototype.setValue = function ( value ) { // eslint-disable-line no-unused-vars
		// STUB
		return {};
	};

	mw.ext.forms.target.FormTarget.prototype.setPropertiesInputValues = function ( configs, values ) { // eslint-disable-line no-unused-vars
		// STUB
	};
}( mediaWiki ) );
