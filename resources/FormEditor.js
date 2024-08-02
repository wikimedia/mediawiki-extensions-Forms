mw.ext.forms.widget.FormEditor = function( cfg ) {
	cfg.expanded = false;
	cfg.padded = true;
	mw.ext.forms.widget.FormEditor.parent.call( this, cfg );

	this.formData = cfg.formData;
	this.cancelRedirect = cfg.cancelRedirect;
	this.successRedirect = cfg.successRedirect;
	this.targetPage = cfg.targetPage;

	this.addToolbar();
	this.addPicker();
	this.addItems();
};

OO.inheritClass( mw.ext.forms.widget.FormEditor, OO.ui.PanelLayout );

mw.ext.forms.widget.FormEditor.prototype.addToolbar = function () {
	this.toolbar = this.makeToolbar();
};

mw.ext.forms.widget.FormEditor.prototype.addItems = function () {
	var config = {};
	if ( !$.isEmptyObject( this.formData ) ) {
		config.data = this.formData;
	}
	this.itemsForm = new mw.ext.forms.form.Items( config );
	this.itemsForm.render();
	this.$element.append( this.itemsForm.$element );

	this.propertiesForm = new mw.ext.forms.form.FormProperties( {
		data: this.formData
	} );
	this.propertiesForm.render();
};

mw.ext.forms.widget.FormEditor.prototype.openSettingsDialog = function () {
	this.openWindow( new mw.ext.forms.dialog.FormPropertiesDialog( {
		form: this.propertiesForm
	} ) );
};

mw.ext.forms.widget.FormEditor.prototype.openElementsDrawer = function ( tool ) {
	this.elementPicker.$element.slideToggle( 200, function() {
		tool.setActive( this.elementPicker.$element.is( ':visible' ) );
	}.bind( this ) );
};

mw.ext.forms.widget.FormEditor.prototype.addPicker = function () {
	this.elementPicker = new mw.ext.forms.widget.ElementPicker(
		mw.ext.forms.widget.FormEditor.static.getElementPickerItems(), true
	);
	this.toolbar.$element.append( this.elementPicker.$element );
	this.elementPicker.$element.hide();
};

mw.ext.forms.widget.FormEditor.prototype.cancelEditing = function () {
	if ( this.cancelRedirect ) {
		window.location.href = this.cancelRedirect;
	}
};

mw.ext.forms.widget.FormEditor.prototype.openSaveDialog = function () {
	this.openWindow(
		new mw.ext.forms.dialog.SaveFormDefinitionDialog( {
			formData: this.formData,
			itemsForm: this.itemsForm,
			propertiesForm: this.propertiesForm,
			targetPage: this.targetPage
		} ),
		function( res ) {
			if ( res && res.action === 'save' ) {
				window.location.href = res.data.result.fullURL || this.successRedirect;
			}
		}
	);
};

mw.ext.forms.widget.FormEditor.prototype.makeToolbar = function () {
	var editor = this,
		toolFactory = new OO.ui.ToolFactory(),
		toolGroupFactory = new OO.ui.ToolGroupFactory(),
		toolbar = new OO.ui.Toolbar( toolFactory, toolGroupFactory );
	toolbar.$element.addClass( 'form-editor-toolbar' );
	this.$element.append( toolbar.$element );

	var settingsTool = function() {
		settingsTool.super.apply( this, arguments );
	};
	OO.inheritClass( settingsTool, OO.ui.Tool );
	settingsTool.static.name = 'settings';
	settingsTool.static.icon = 'settings';
	settingsTool.static.title = mw.msg( 'forms-form-editor-form-props-label' );
	settingsTool.prototype.onSelect = function() {
		this.setActive( false );
		editor.openSettingsDialog();
	};
	settingsTool.prototype.onUpdateState = function () {};
	toolFactory.register( settingsTool );

	var elementsPicker = function() {
		elementsPicker.super.apply( this, arguments );
	};
	OO.inheritClass( elementsPicker, OO.ui.Tool );
	elementsPicker.static.name = 'elements';
	elementsPicker.static.icon = 'puzzle';
	elementsPicker.static.displayBothIconAndLabel = true;
	elementsPicker.static.title = mw.msg( 'forms-form-editor-form-items-label' );
	elementsPicker.prototype.onSelect = function() {
		editor.openElementsDrawer( this );
	};
	elementsPicker.prototype.onUpdateState = function () {};
	toolFactory.register( elementsPicker );

	var cancelTool = function() {
		cancelTool.super.apply( this, arguments );
	};
	OO.inheritClass( cancelTool, OO.ui.Tool );
	cancelTool.static.name = 'cancel';
	cancelTool.static.icon = 'cancel';
	cancelTool.static.flags = [ 'destructive' ];
	cancelTool.static.title = mw.msg( 'forms-editor-toolbar-cancel' );
	cancelTool.prototype.onSelect = function() {
		editor.cancelEditing();
	};
	cancelTool.prototype.onUpdateState = function () {};
	toolFactory.register( cancelTool );

	var saveTool = function() {
		saveTool.super.apply( this, arguments );
	};
	OO.inheritClass( saveTool, OO.ui.Tool );
	saveTool.static.name = 'save';
	saveTool.static.title = mw.msg( 'forms-editor-toolbar-save' );
	saveTool.static.flags = [ 'primary', 'progressive' ];
	saveTool.prototype.onSelect = function() {
		this.setActive( false );
		editor.openSaveDialog();
	};
	saveTool.prototype.onUpdateState = function () {};
	toolFactory.register( saveTool );

	toolbar.setup( [
		{
			type: 'bar',
			include: [ 'elements' ]
		},
		{
			name: 'actions',
			classes: [ 'actions' ],
			type: 'bar',
			include: [ 'settings', 'cancel', 'save' ]
		}
	] );
	toolbar.initialize();

	return toolbar;
};

mw.ext.forms.widget.FormEditor.static.getElementPickerItems = function () {
	var grouped = {};
	for( var name in mw.ext.forms.registry.Type.registry ) {
		if ( !mw.ext.forms.registry.Type.registry.hasOwnProperty( name ) ) {
			continue;
		}
		var element =  mw.ext.forms.registry.Type.registry[name];
		if ( name === '_default' ) {
			continue;
		}
		if ( element.isSystemElement() ) {
			continue;
		}
		if ( element.isHidden() ) {
			continue;
		}
		var group = element.getGroup(),
			groupObject = mw.ext.forms.registry.ElementGroup.registry[group];
		if ( !grouped.hasOwnProperty( group ) ) {
			grouped[group] = {
				group: groupObject,
				items: []
			};
		}


		grouped[group].items.push( {
			data: name,
			label: element.getDisplayName(),
			icon: element.getIcon(),
			type: mw.ext.forms.widget.formElement.FormElementGroup.static.getElementType( element )
		} );
	}

	return grouped;
};

mw.ext.forms.widget.FormEditor.prototype.openWindow = function( window, callback ) {
	var windowManager = new OO.ui.WindowManager();
	$( 'body' ).append( windowManager.$element );
	windowManager.addWindows( [ window ] );
	if ( callback ) {
		windowManager.openWindow( window ).closed.then( callback );
	} else {
		windowManager.openWindow( window );
	}
};
