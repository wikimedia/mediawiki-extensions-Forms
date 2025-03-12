var def = { // eslint-disable-line no-var, no-implicit-globals, no-unused-vars
	title: 'TestFormForTag',
	extends: 'TestForm',
	showTitle: true,
	target: {
		type: 'json-on-wikipage',
		title: 'Customer_{{customerName}}',
		afterAction: {
			type: 'callback',
			callback: function () {
				this.$element.children().remove();
				this.$element.append( new OO.ui.LabelWidget( {
					label: 'Thank you for filling the form'
				} ).$element );
			}
		}
	},
	listeners: {
		submit: function () {
			// Do stuff on submit - scope: form
			// Called after validation pass
		},
		reset: function () {
			// Do stuff on reset - scope: form
		},
		cancel: function () {
			// Do stuff on reset - scope: form
		}
	}
};
