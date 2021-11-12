( function ( mw, $, undefined ) {
	mw.ext.forms.widget.formElement.FormEditorElementDescriptor = function( cfg ) {
		mw.ext.forms.widget.formElement.FormEditorElementDescriptor.parent.call( this, cfg );

		this.$input.remove();
		this.elementName = cfg.elementName;
		this.form = cfg.form;
		this.identifier = cfg.identifier;
		this.group = cfg.group;
		this.values = cfg.values || {};
		this.subitems = {};
		this.initialSubitemAdded = false;
		this.parent = cfg.parent || null;
		this.changeIgnore = false;
		this.$sortableContainer = $( '<div>' );

		this.element = mw.ext.forms.registry.Type.registry[this.elementName];
		this.configInputs = {};
		this.renderedLayout = null;

		this.ensureRequiredValues();

		this.$element.addClass( 'ext-forms-form-editor-element-descriptor' );
	};

	OO.inheritClass( mw.ext.forms.widget.formElement.FormEditorElementDescriptor, OO.ui.InputWidget );

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.setValue = function( value ) {
		if ( !value ) {
			return;
		}

		this.values = value;
		if ( this.supportsSubitems() && !this.initialSubitemAdded ) {
			this.addSubitems( value );
			this.initialSubitemAdded = true;
		}
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.ignoreChanges = function( value ) {
		this.changeIgnore = !!value;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.setPropertiesInputValues = function( overrideValues ) {
		overrideValues = overrideValues || {};
		var values = this.values;

		values = this.groupCustom( values );
		for( var key in this.configInputs ) {
			if ( overrideValues.hasOwnProperty( key ) ) {
				this.configInputs[key].setValue( overrideValues[key] );
				values[key] = overrideValues[key];
				continue;
			}
			if ( values.hasOwnProperty( key ) ) {
				this.configInputs[key].setValue( values[key] );
			}
		}
		if ( this.form.selectedTarget ) {
			this.form.selectedTarget.setPropertiesInputValues( this.configInputs, values );
		}

		this.updateValues();
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.groupCustom = function( values ) {
		var reserved = [ 'classes', 'listeners' ], props = {};
		for ( var key in values ) {
			if ( !values.hasOwnProperty( key ) ) {
				continue;
			}
			if ( !key.startsWith( 'widget_' ) ) {
				continue;
			}
			var realKey = key.split( 'widget_' ).pop();
			if ( reserved.indexOf( realKey ) !== -1 ) {
				continue;
			}

			props[key] = values[key];
			delete( values[key] );
		}
		values.widgetCustomProps = props;

		return values;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getValue = function( ref ) {
		ref = ref || false;
		if ( ref ) {
			return this.values;
		}

		// Do not return by ref by default
		return $.extend( {}, this.values );
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.ensureRequiredValues = function( values ) {
		values = values || this.values;

		if ( !values.hasOwnProperty( 'name' ) ) {
			values.name = this.form.getRandomName( this.element.getType(), Math.floor( Math.random() * (9999 - 100) + 100) );
		}
		if ( !values.hasOwnProperty( 'type' ) ) {
			values.type = this.element.getType();
		}

		return values;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.updateValues = function( forceValues ) {
		if ( this.changeIgnore ) {
			return;
		}
		forceValues = forceValues || false;

		var value = {},
			originalName = this.values.name || null,
			originalType = this.values.type || null;

		for( var name in this.configInputs ) {
			if ( !this.configInputs.hasOwnProperty( name ) ) {
				continue;
			}
			if ( forceValues && this.values.hasOwnProperty( name ) ) {
				value[name] = this.values[name];
				continue;
			}
			value[name] = this.configInputs[name].getValue();
		}

		value = this.ensureRequiredValues( value );

		this.values = value;
		if ( this.supportsSubitems() ) {
			this.updateSubitemValues();
		}
		if ( this.values.name !== originalName ) {
			this.emit( 'nameChange', this, originalName, this.values.name );
		}
		if ( originalType !== null && this.values.type !== originalType ) {
			this.emit( 'typeChange', this, originalType, this.values.type );
		} else {
			this.emit( 'propChange', this );
		}
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.updateSubitemValues = function() {
		var subitemValues = [],
			subitems = this.group.getOrderedItems( this.subitems, this.$sortableContainer );

		for( var subitemId in subitems ) {
			var subitem = subitems[subitemId];
			subitemValues.push( subitem.getValue() );
		}
		this.values[this.getSubitemProp()] = subitemValues;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.supportsSubitems = function() {
		return this.getElement().getSubitemConfig() !== false;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getSubitemProp = function() {
		if ( this.supportsSubitems() ) {
			return this.getElement().getSubitemConfig().propName;
		}
		return '';
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.addSubitems = function( value ) {
		var config = this.getElement().getSubitemConfig();
		if ( !config.hasOwnProperty( 'propName' ) ) {
			return;
		}
		if ( !value.hasOwnProperty( config.propName ) ) {
			return;
		}

		for ( var i = 0; i < value[config.propName].length; i++ ) {
			var subitem = value[config.propName][i];
			this.addSubitem( subitem.type, subitem );
		}
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.addSubitem = function( type, value ) {
		var element = mw.ext.forms.registry.Type.lookup( type );
		value = $.extend( {}, element.getDefaultValue(), value || {} );

		var config = this.getElement().getSubitemConfig();
		if ( !config.hasOwnProperty( 'propName' ) ) {
			return;
		}

		var identifier = this.group.getNewIdentifier();
		var subitemDescriptor = new mw.ext.forms.widget.formElement.FormEditorElementDescriptor( {
			identifier: identifier,
			elementName: type,
			group: this.group,
			form: this.form,
			parent: this,
			values: value
		} );

		subitemDescriptor.connect( this, {
			propChange: function() {
				this.updateSubitemValues();
				this.emit( 'propChange', this );
			}
		} );

		this.subitems[identifier] = subitemDescriptor;
		if ( value ) {
			this.subitems[identifier].setValue( value );
		}

		if ( this.initialSubitemAdded ) {
			this.updateSubitemValues();
			this.emit( 'addSubitem', this, subitemDescriptor );
		}

		return subitemDescriptor;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.removeSubitem = function( identifier ) {
		delete( this.subitems[identifier] );
		this.updateSubitemValues();
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.findSubitemByName = function( name ) {
		for ( var id in this.subitems ) {
			var subitem = this.subitems[id];
			if ( subitem.getValue().hasOwnProperty( 'name' ) && subitem.getValue().name === name ) {
				return subitem;
			}
		}
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getParentDescriptor = function() {
		return this.parent;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getSubitems = function() {
		return this.subitems;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getValidity = function() {
		var dfd = $.Deferred(), toCheck = $.extend( {}, this.configInputs );

		this.form.validateInternally( toCheck, dfd );

		return dfd.promise();
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getValueForPreview = function() {
		var value = this.getValue();
		value.showOn = [ 'edit', 'create' ];
		value.editableOn = ['edit', 'create' ];

		if ( this.supportsSubitems() ) {
			var subitemWrappers = [];
			for ( var subitem in this.subitems ) {
				if ( !this.subitems.hasOwnProperty( subitem ) ) {
					continue;
				}

				subitemWrappers.push( {
					name: this.subitems[subitem].getIdentifier() + '_wrapper',
					type: 'form_element_wrapper',
					noLayout: true,
					original: this.subitems[subitem].getValueForPreview(),
					identifier: this.subitems[subitem].getIdentifier(),
					formDescriptor: this.subitems[subitem],
					group: this.group
				} );
			}

			value[this.getSubitemProp()] = subitemWrappers;
		}

		return value;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getIdentifier = function() {
		return this.identifier;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getElementName = function() {
		return this.elementName;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getElement = function() {
		return this.element;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.renderOptions = function( layout, overrideValues ) {
		this.ignoreChanges( true );

		this.renderedLayout = new OO.ui.FieldsetLayout( {
			items: layout.getItems()
		} );

		var config = this.element.getElementConfig();
		if ( this.form.selectedTarget ) {
			config = this.form.selectedTarget.getWidgetFields( config );
		}

		this.configInputs = {};
		var grouped = this.groupToTabs( config ), tabs = [];
		for ( var group in grouped ) {
			if ( !grouped.hasOwnProperty( group ) ) {
				continue;
			}
			var groupLayout = new OO.ui.FieldsetLayout();
			this.configInputs = $.extend(
				{}, this.configInputs,
				this.form.parseItems( grouped[group], groupLayout, true )
			);

			tabs.push( new OO.ui.TabPanelLayout( group, {
				expanded: false,
				framed: false,
				padded: true,
				label: group,
				content: [ groupLayout ]
			} ) );
		}
		var index = new OO.ui.IndexLayout( { expanded: false } );
		index.addTabPanels( tabs );
		var panel = new OO.ui.PanelLayout( {
			expanded: false,
			framed: false,
			classes: [ 'forms-form-layout-inner' ],
			content: [ index ]
		} );
		this.renderedLayout.addItems( panel );

		// Add values to the option controls just rendered
		this.setPropertiesInputValues( overrideValues );

		// Now that initial values are set we can connect all option controls, in order
		// to update values if any of them changes
		for ( var name in this.configInputs ) {
			this.configInputs[name].connect( this, {
				change: function() {
					this.updateValues();
				}
			} );
		}

		// This is kinda round-about, but we dont want the descriptor to return the actual layout,
		// just to add items to the one that is passed
		layout.addItems( this.renderedLayout.getItems() );
		this.ignoreChanges( false );
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.groupToTabs = function( items ) {
		var grouped = {};
		for ( var i = 0; i < items.length; i++ ) {
			var item = items[i];
			var group = item.hasOwnProperty( 'widget_data' ) && item.widget_data.hasOwnProperty( 'tab' ) ? item.widget_data.tab : 'other';
			if ( !grouped.hasOwnProperty( group ) ) {
				grouped[group] = [];
			}
			grouped[group].push( item );
		}

		return grouped;
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.getInputsFromIndexLayout = function( layout ) {
		var tabs = layout.stackLayout.getItems();
		console.log( tabs );
		for ( var i = 0; i < tabs.length; i++ ) {
			var tab = tabs[i];
			console.log( tab.items );
		}

		return [];
	};

	mw.ext.forms.widget.formElement.FormEditorElementDescriptor.prototype.setSortableContainer = function( $container ) {
		this.$sortableContainer = $container;
	};
} )( mediaWiki, jQuery );
