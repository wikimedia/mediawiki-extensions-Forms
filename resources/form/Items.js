mw.ext.forms.form = mw.ext.forms.form || {};
mw.ext.forms.form.Items = function( cfg ) {
	cfg = cfg || {};
	cfg.definition = {
		buttons: [],
		items:  [
			{
				type: 'text',
				hidden: true,
				name: 'lang',
				widget_hidden: true,
				value: 'json'
			}, {
				type: 'form_element_group',
				name: 'items',
				noLayout: true
			}
		]
	};
	mw.ext.forms.form.Items.parent.call( this, cfg );
};

OO.inheritClass( mw.ext.forms.form.Items, mw.ext.forms.standalone.Form );