( function ( mw, $, d ) {
	mw.ext.forms.mixin.DefinitionParser = function() {
		this.title = this.definition.title || '';
		this.showTitle = this.definition.showTitle || false;
		this.showFormName = this.definition.showFormName || false;
		this.sealAfterCreation = this.definition.sealAfterCreation || false;
		this.buttonsFloat = this.definition.buttonsFloat || false;
		this.target = this.definition.target || '';
		this.rawItems = this.definition.items;
		this.formListeners = this.definition.listeners || {};
		this.enableEditSummary = this.definition.editSummary || false;
		this.items = {
			inputs: {},
			statics: {}
		};

		this.editMode = this.action !== 'view';

		this.typeMap = mw.config.get( 'FormsTypeMap' );
		this.parseDefinition();
	};

	OO.initClass( mw.ext.forms.mixin.DefinitionParser );

	mw.ext.forms.mixin.DefinitionParser.static.reservedKeywords = [
		"type", "label", "name", "help", "listeners", "showOn", "editableOn", "noLayout", "style", "level"
	];

	mw.ext.forms.mixin.DefinitionParser.prototype.parseDefinition = function() {
		if ( this.showTitle ) {
			this.layout.setLabel( this.title );
		}
		if ( this.showFormName ) {
			this.addFormNameWidget();
		}

		this.parseItems( this.rawItems, this.layout );
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.parseItems = function( items, layout ) {
		for( var i = 0; i < items.length; i++ ) {
			var item = items[i];

			var separatedConfig = this.separateConfigs( item );
			var itemConfig = separatedConfig.itemConfig;
			var widgetConfig = separatedConfig.widgetConfig;

			if ( !itemConfig.hasOwnProperty( 'type' ) ) {
				// No type set
				continue;
			}

			if ( this.shouldShow( itemConfig.showOn ) === false ) {
				// Set not to show
				continue;
			}

			if ( itemConfig.type.substring( 0, 6 ) === 'layout' ) {
				// "Regular" layouts, with items parameter
				if ( widgetConfig.hasOwnProperty( 'items' ) ) {
					widgetConfig.layoutItems = widgetConfig.items;
					delete( widgetConfig.items );
				}
				widgetConfig.expanded = false;
			}

			var widgetFunc = this.getWidgetFromType( itemConfig.type );
			if ( widgetFunc === null ) {
				continue;
			}

			if ( itemConfig.hasOwnProperty( 'level' ) ) {
				widgetConfig.classes = widgetConfig.classes || [];
				widgetConfig.classes.push( 'level-' + itemConfig.level );
			}

			// Inject form context into any widget displayed in it
			widgetConfig.form = this;
			try {
				var widgetInstance = new widgetFunc( widgetConfig );
			} catch( error ) {
				console.log( error );
				continue;
			}

			if ( widgetInstance instanceof OO.ui.Layout ) {
				this.parseLayout( itemConfig.name, widgetConfig, widgetInstance, layout );
				continue;
			}

			if ( this.editMode ) {
				this.addEditWidget( itemConfig, widgetConfig, widgetInstance, layout );
			} else {
				this.addViewWidget( itemConfig, widgetConfig, widgetInstance, layout );
			}
		}
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.parseLayout = function( name, config, layout, hostLayout ) {
		name = name || this.getNameForStatic();
		if ( layout instanceof OO.ui.BookletLayout ) {
			this.parseBooklet( config.pages, layout );
			layout = this.wrapInPanel( layout, config.framed );
		} else if( layout instanceof OO.ui.IndexLayout ) {
			this.parseIndexLayout( config.tabs, layout );
			layout = this.wrapInPanel( layout, config.framed );
		} else if ( config.hasOwnProperty( 'layoutItems' ) ) {
			for( var li = 0; li < config.layoutItems.length; li++ ) {
				var layoutItem = config.layoutItems[li];
				if( !layoutItem.hasOwnProperty( 'noLayout' ) || layoutItem.noLayout !== false ) {
					layoutItem.noLayout = true;
				}
			}
			this.parseItems( config.layoutItems, layout );
		} else {
			// No special layout and no items - nothing to display
			return;
		}
		this.items.statics[name] = layout;
		hostLayout.addItems( [ layout ] );
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.wrapInPanel = function( widget, framed ) {
		return new OO.ui.PanelLayout( {
			expanded: false,
			framed: !!framed,
			classes: [ 'forms-form-layout-inner' ],
			content: [ widget ]
		} );
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.addFormNameWidget = function() {
		if ( this.action === 'view' ) {
			this.layout.addItems( [ new OO.ui.HorizontalLayout( {
				classes: [ 'forms-form-view-layout' ],
				items: [
					new OO.ui.LabelWidget( {
						label: mw.message( 'forms-form-form-name-label' ).text(),
						classes: [ 'view-label' ]
					} ),
					new OO.ui.LabelWidget( {
						label: this.definitionName
					} )
				]
			} ) ] );
		} else if ( this.action === 'edit' ) {
			this.layout.addItems( [
				new OO.ui.FieldLayout( new OO.ui.TextInputWidget( {
					value: this.definitionName,
					disabled: true
				} ), {
					label: mw.message( 'forms-form-form-name-label' ).text()
				} )
			] );
		}
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.addViewWidget = function( config, widgetConfig, widget, layout ) {
		if ( config.hasOwnProperty( 'name' ) && this.data.hasOwnProperty( config.name ) ) {
			widget.setValue( this.data[config.name], config, widgetConfig );
			this.items.inputs[config.name] = widget;
		} else {
			var name = config.name || this.getNameForStatic();
			this.items.statics[name] = widget;
		}

		if ( typeof config.listeners === "object" ) {
			this.addListenersToWidget( widget, config.listeners );
		}

		if ( config.noLayout === true || ( config.noLayout !== false && !config.hasOwnProperty( 'name' ) ) ) {
			layout.addItems( [ widget ] );
		} else {
			layout.addItems( [ new OO.ui.HorizontalLayout( {
				classes: [ 'forms-form-view-layout' ],
				items: [
					new OO.ui.LabelWidget( {
						label: config.label || config.name,
						classes: [ 'view-label' ]
					} ),
					widget
				]
			} ) ] );
		}
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.addEditWidget = function( config, widgetConfig, widget, layout ) {
		var isInput = widget instanceof OO.ui.InputWidget;
		if ( isInput && !config.name ) {
			// All inputs must have name
			return;
		}

		if ( isInput ) {
			if ( !this.shouldEdit( config.editableOn ) ) {
				widget.setDisabled( true );
			}
			if ( this.data.hasOwnProperty( config.name ) ) {
				widget.setValue( this.data[config.name] );
			}
			this.items.inputs[config.name] = widget;
		} else {
			var name = config.name || this.getNameForStatic();
			this.items.statics[name] = widget;
		}

		if ( config.style ) {
			widget.$element.attr( 'style', config.style );
		}

		if ( typeof config.listeners === "object" ) {
			this.addListenersToWidget( widget, config.listeners );
		}

		// If explicitly set, or if widget is input and its not explicitly set to false
		if ( config.noLayout === true || ( config.noLayout !== false && !isInput ) ) {
			layout.addItems( [ widget ] );
		} else {
			layout.addItems( [ new OO.ui.FieldLayout( widget, {
				classes: [ 'forms-form-edit-layout' ],
				label: config.label || config.name,
				help: config.help || ''
			} ) ] );
		}

		widget.emit( 'render', widget );
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.getWidgetFromType = function( type ) {
		if ( !this.typeMap[type] ) {
			type = "_default";
		}

		var widgets = this.typeMap[type];
		var widget;
		if ( typeof widgets === 'object' ) {
			if ( this.action === 'view' && widgets.hasOwnProperty( 'view' ) ) {
				widget = widgets.view;
			} else if ( this.action !== 'view' && widgets.hasOwnProperty( 'edit' ) ) {
				widget = widgets.edit;
			}
		}
		if ( typeof widgets === 'string' ) {
			widget = widgets;
		}
		if ( !widget ) {
			// No widget for current action
			return null;
		}

		return this.funcFromString( widget );
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.funcFromString = function( cb ) {
		var parts = cb.split( '.' );
		var func = window[parts[0]];
		for( var i = 1; i < parts.length; i++ ) {
			func = func[parts[i]];
		}
		return func;
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.shouldShow = function( showOn ) {
		return this._shouldAction( showOn );
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.shouldEdit = function( editableOn ) {
		return this._shouldAction( editableOn );
	};

	mw.ext.forms.mixin.DefinitionParser.prototype._shouldAction = function( cond ) {
		if ( !cond ) {
			return true;
		}

		if ( typeof cond === 'function' ) {
			return cond();
		}

		if ( $.isArray( cond ) === false ) {
			cond = [ cond ];
		}

		if ( cond.indexOf( this.action ) !== -1 ) {
			return true;
		}

		return false;
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.separateConfigs = function( item ) {
		var itemConfig = {};
		var widgetConfig = {};

		for( var name in item ) {
			if ( !item.hasOwnProperty( name ) ) {
				continue;
			}
			var value = item[name];

			if ( mw.ext.forms.mixin.DefinitionParser.static.reservedKeywords.indexOf( name ) !== -1 ) {
				itemConfig[name] = value;
			} else {
				// If a reserved keyword should actually go to the widget config
				// it must be prefixed with "widget_"
				if ( name.substring( 0, 7 ) === 'widget_' ) {
					name = name.substring( 7 );
				}
				widgetConfig[name] = value;
			}
		}

		return {
			itemConfig: itemConfig,
			widgetConfig: widgetConfig
		}
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.getItem = function( name ) {
		if ( name in this.items.inputs ) {
			return this.items.inputs[name];
		}
		if ( name in this.items.statics ) {
			return this.items.statics[name];
		}
		return null;
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.getItems = function() {
		return this.items;
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.addListenersToWidget = function( widget, listeners ) {
		for( var event in listeners ) {
			if ( !listeners.hasOwnProperty( event ) ) {
				continue;
			}
			var handler = listeners[event];

			widget.on( event, handler.bind( this ) );
		}
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.getInputs = function() {
		return this.items.inputs;
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.getStatics = function() {
		return this.items.statics;
	};

	mw.ext.forms.mixin.DefinitionParser.prototype.getNameForStatic = function( num ) {
		num = num || 1;
		var name = 'static-' + num;
		if ( this.items.statics.hasOwnProperty( name ) ) {
			num++;
			return this.getNameForStatic( num );
		}
		return name;
	}

} )( mediaWiki, jQuery, document );