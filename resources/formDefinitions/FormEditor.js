return {
	title: mw.message( 'forms-form-editor-title' ).text(),
	showTitle: false,
	system: true,
	buttons: [ 'submit', 'cancel' ],
	target: {
		type: 'form-definition',
		title: '{{form_name}}'
	},
	items: [
		{
			type: 'text',
			hidden: true,
			name: 'lang',
			widget_hidden: true,
			value: 'json'
		},
		{
			type: 'text',
			required: true,
			label: mw.message( "forms-form-editor-form-name-label" ).text(),
			name: "form_name",
			help: mw.message( "forms-form-editor-form-name-help" ).text(),
			editableOn: [ 'create' ]
		}, {
			type: 'section_label',
			title: mw.message( "forms-form-editor-form-props-label" ).text()
		}, {
			type: 'formImport',
			form: 'FormProperties'
		},{
			type: 'section_label',
			title: mw.message( "forms-form-editor-form-items-label" ).text()
		}, {
			type: 'form_element_group',
			name: 'items',
			noLayout: true
		}
	],
	listeners:  {
		parseComplete: function( items ) {
			var targetPicker = this.getItem( 'target.type' ),
				options = [];
			for( var name in mw.ext.forms.registry.Target.registry ) {
				if ( !mw.ext.forms.registry.Target.registry.hasOwnProperty( name ) ) {
					continue;
				}
				var target = new mw.ext.forms.registry.Target.registry[name]();
				options.push( {
					data: target.getName(),
					label: target.getDisplayName()
				} );
			}
			targetPicker.setOptions( options );
			if ( this.data.hasOwnProperty( 'target' ) ) {
				targetPicker.setValue( this.data.target.type );
				if ( this.selectedTarget !== null ) {
					this.selectedTarget.setValue( this.data.target );
				}
			}
		},
		beforeSubmitData: function( data ) {
			var dfd = $.Deferred();

			if ( this.selectedTarget !== null ) {
				data.target = this.selectedTarget.getValue();
			}
			if ( !data.show_target_afterAction ) {
				delete( data.target.afterAction );
			}

			dfd.resolve( data );
			return dfd;
		}
	}
};
