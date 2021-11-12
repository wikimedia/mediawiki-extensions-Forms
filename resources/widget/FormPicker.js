( function ( mw, $, undefined ) {
	mw.ext.forms.widget.FormPicker = function( config ) {
		mw.ext.forms.widget.FormPicker.parent.call( this, {} );

		var definitions = config.forms || [];
		this.label = config.label || '';
		if ( config.hasOwnProperty( 'size' ) ) {
			this.$element.css( 'width', config.size );
		}

		if ( definitions.length > 0 ) {
			this.showPicker( definitions );
		} else {
			this.getAvailableDefinitions();
		}

		this.$element.addClass( 'mw-ext-forms-form-picker' );
	};
	OO.inheritClass( mw.ext.forms.widget.FormPicker, OO.ui.Widget );

	mw.ext.forms.widget.FormPicker.static.tagName = 'div';

	mw.ext.forms.widget.FormPicker.prototype.makePicker = function( definitions ) {
		var items = [];
		for( var i = 0; i < definitions.length; i++ ) {
			items.push( new OO.ui.MenuOptionWidget( {
				data: definitions[i],
				label: definitions[i],
				icon: 'article'
			} ) );
		}

		this.picker = new OO.ui.DropdownWidget( {
			label: mw.message( 'forms-form-picker-picker-label' ).text(),
			menu: {
				items: items
			}
		} );

		this.picker.menu.on( 'select', function( item ) {
			this.emit( 'definitionSelected', item.getData() );
		}.bind( this ) );
	};

	mw.ext.forms.widget.FormPicker.prototype.makeLayout = function() {
		this.layout = new OO.ui.FieldLayout( this.picker, {
			label: this.label || mw.message( 'forms-form-picker-layout-label' ).text(),
			help: mw.message( 'forms-form-picker-layout-help' ).text()
		} );
	};

	mw.ext.forms.widget.FormPicker.prototype.getAvailableDefinitions = function() {
		this.executeAPICall( {
			type: 'query-available'
		} ).done( function( response ) {
			if ( response.success ) {
				this.showPicker( response.result );
			} else {
				this.showApiError( response.error );
			}
		}.bind( this ) ).fail( function() {
			this.showApiError( mw.message( 'forms-generic-api-error' ).text() );
		}.bind( this ) );
	};

	mw.ext.forms.widget.FormPicker.prototype.showPicker = function( definitions ) {
		this.makePicker( definitions );
		this.makeLayout();
		this.$element.append(
			this.layout.$element
		);
	};

	mw.ext.forms.widget.FormPicker.prototype.selectFirst = function() {
		var item = this.picker.menu.findFirstSelectableItem();
		this.picker.menu.selectItem( item );
	};

	mw.ext.forms.widget.FormPicker.prototype.executeAPICall = function( data ) {
		data = data || {};
		data = $.extend( {
			action: 'forms-get-definitions'
		}, data );

		var api = new mw.Api();
		return api.get( data );
	};

	mw.ext.forms.widget.FormPicker.prototype.showApiError = function( error ) {
		console.log( error );
	}

} )( mediaWiki, jQuery );