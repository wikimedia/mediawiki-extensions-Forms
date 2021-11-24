( function ( mw, $, undefined ) {
	mw.ext.forms.target.Template = function( form, items ) {
		mw.ext.forms.target.Template.parent.call( this, form, items );

		this.fields = [];
	};

	OO.inheritClass( mw.ext.forms.target.Template, mw.ext.forms.target.FormTarget );

	mw.ext.forms.target.Template.prototype.init = function() {
		this.items['target.template'].connect( this, {
			change: 'onTemplateChange'
		} );
		this.onTemplateChange( this.items['target.template'].getValue() );
	};

	mw.ext.forms.target.Template.prototype.getName = function() {
		return 'template';
	};

	mw.ext.forms.target.Template.prototype.getDisplayName = function() {
		return mw.message( 'forms-form-editor-prop-targettypetemplate' ).text();
	};

	mw.ext.forms.target.Template.prototype.getAdditionalFields = function() {
		return [ {
			type: 'text',
			name: 'target.title',
			required: true,
			label: mw.message( 'forms-form-editor-prop-targettitle' ).text()
		}, {
			type: 'title',
			name: 'target.template',
			namespace: 10,
			required: true,
			label: mw.message( 'forms-form-editor-prop-targettemplate' ).text()
		}, {
			type: 'button',
			name: 'target.importfromtemplate',
			widget_flags: [ 'primary', 'progressive' ],
			widget_label: mw.message( 'forms-form-editor-button-import-template' ).text(),
			classes: [ 'field-button' ],
			widget_disabled: true,
			listeners: {
				click: function() {
					if ( !this.selectedTarget instanceof mw.ext.forms.target.Template ) {
						return;
					}
					OO.ui.confirm( mw.message( 'forms-form-editor-confirm-import-template').text() )
						.done( function ( confirmed ) {
							if ( confirmed ) {
								this.selectedTarget.insertFromTemplate( this );
							}
						}.bind( this ) );
				}
			}
		} ];
 	};

	mw.ext.forms.target.Template.prototype.getWidgetFields = function( config ) {
		var values = this.form.getItem( 'items' ).getValue(),
			filteredFields = [].concat( this.fields );

		// TODO: Do not allow same field two times - hard to do
		//filteredFields = this.filterOutUsedFields( values, filteredFields );

		if ( filteredFields.length === 0 ) {
			return config;
		}

		var options = [ {
			data: '-',
			label: mw.message( 'forms-form-editor-target-template-field-manual' ).text()
		} ];
		for ( var x = 0; x < filteredFields.length; x++ ) {
			options.push( {
				data: filteredFields[x]
			} );
		}
		for ( var i = 0; i < config.length; i++ ) {
			if ( config[i].name === 'name' ) {
				config.splice( i + 1, 0, {
					type: 'dropdown',
					options: options,
					name: 'template_field',
					label: mw.message( 'forms-form-editor-target-template-field' ).text()
				} );
				break;
			}
		}

		return config;
	};

	mw.ext.forms.target.Template.prototype.setPropertiesInputValues = function( configs, values ) {
		if ( !configs.hasOwnProperty( 'name' ) || !configs.hasOwnProperty( 'template_field' ) ) {
			return;
		}

		configs.template_field.connect( this,  {
			change: function ( value ) {
				this.controlNameWidget( configs, value );
			}
		} );

		if ( this.fields.indexOf( configs.name.getValue() ) !== -1 ) {
			configs.template_field.setValue( configs.name.getValue() );
		}

		this.controlNameWidget( configs, configs.template_field.getValue() );
	};

	mw.ext.forms.target.Template.prototype.controlNameWidget = function( configs, value ) {
		if ( value === '-' ) {
			configs.name.$element.parents( '.control-wrap' ).show();
			return;
		}
		configs.name.$element.parents( '.control-wrap' ).hide();
		configs.name.setValue( value );
	};

	mw.ext.forms.target.Template.prototype.filterOutUsedFields = function( values, fields ) {
		for ( var i = 0; i < values.length; i++ ) {
			if ( Object.prototype.toString.call( values[i] ) !== '[object Object]' ) {
				// NOT OBJECT
				continue;
			}
			for ( var key in values[i] ) {
				if ( !values[i].hasOwnProperty( key ) ) {
					continue;
				}
				if ( key === 'name' ) {
					var index = fields.indexOf( values[i].name );
					if ( index !== -1 ) {
						fields.splice( index, 1 );
					}
				} else if ( Array.isArray( values[i][key] ) ) {
					this.filterOutUsedFields( values[i][key], fields );
				}
			}
		}

		return fields;
	};

	mw.ext.forms.target.Template.prototype.getValue = function() {
		return {
			type: this.getName(),
			title: this.items['target.title'].getValue(),
			template:this. items['target.template'].getValue()
		};
	};

	mw.ext.forms.target.Template.prototype.getFields = function() {
		return this.fields;
	};

	mw.ext.forms.target.Template.prototype.setValue = function( value ) {
		if ( value.hasOwnProperty( 'title' ) && this.items.hasOwnProperty( 'target.title' ) ) {
			this.items['target.title'].setValue( value.title );
		}

		if ( value.hasOwnProperty( 'template' ) && this.items.hasOwnProperty( 'target.template' ) ) {
			this.items['target.template'].setValue( value.template );
			this.onTemplateChange( value.template );
		}
	};

	mw.ext.forms.target.Template.prototype.onTemplateChange = function( value ) {
		this.items['target.importfromtemplate'].setDisabled( true );
		if ( !value ) {
			return;
		}
		this.fields = [];
		var title = mw.Title.makeTitle( 10, value );
		new mw.Api().get( {
			action: 'query',
			prop: 'revisions',
			titles: title.getPrefixedText(),
			rvprop: 'content',
			formatversion: 2
		} ).done( function( response ) {
			if ( !response.hasOwnProperty( 'query' ) ) {
				return;
			}
			var page = response.query.pages[0];
			if ( !page.hasOwnProperty( 'revisions' ) || !Array.isArray( page.revisions ) ) {
				return;
			}

			this.fields = this.parseTemplateText( page.revisions[0].content );
			this.items['target.importfromtemplate'].setDisabled( false );
		}.bind( this ) );
	};

	mw.ext.forms.target.Template.prototype.parseTemplateText = function( text ) {
		var regex = /{{{(.*?)(\|.*?|)}}}/gm,
			matches = [],
			fields = [];
		while( matches = regex.exec( text ) ) {
			fields.push( matches[1] );
		}

		return fields;
	};

	mw.ext.forms.target.Template.prototype.insertFromTemplate = function() {
		var group = this.form.getItem( 'items' ),
			value = [];
		group.clearElements();
		for( var i = 0; i < this.fields.length; i++ ) {
			value.push( {
				type: 'text',
				name: this.fields[i]
			} );
		}

		group.setValue( value );
	};

	mw.ext.forms.registry.Target.register( 'template', mw.ext.forms.target.Template );
} )( mediaWiki, jQuery );
