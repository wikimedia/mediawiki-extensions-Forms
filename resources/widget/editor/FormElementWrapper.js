( function ( mw, $, undefined ) {
	mw.ext.forms.widget.formElement.FormElementWrapper = function( cfg ) {
		mw.ext.forms.widget.formElement.FormElementWrapper.parent.call( this, cfg );
		this.origin = cfg.original;
		this.identifier = cfg.identifier;
		this.formDescriptor = cfg.formDescriptor;
		this.name = this.origin.name || '';
		this.form = cfg.form;
		this.group = cfg.group;
		this.optionsPanel = new OO.ui.FieldsetLayout();
		this.isLayout = this.formDescriptor.getElement() instanceof mw.ext.forms.formElement.FormLayoutElement;

		OO.ui.mixin.PopupElement.call( this, {
			padded: true,
			$content: this.optionsPanel.$element
		} );
		this.popup.$element
			.addClass( 'oo-ui-popupButtonWidget-framed-popup' )
			.addClass( 'ext-forms-editor-options-popup' );

		this.layout = new OO.ui.FieldsetLayout( { classes: [ 'wrapper-original' ] } );
		this.render = this.form.parseItems( [ this.origin ], this.layout, true );
		this.render = this.render[Object.keys( this.render )[0] ];

		this.$overlay = $( '<div>' ).addClass( 'wrapper-overlay' );
		this.$overlay.append( this.getNameAndToolsBadge().$element );

		if ( !this.isLayout ) {
			this.$overlay.append( new OO.ui.IconWidget( {
				icon: 'draggable',
				title: 'Drag to reorder',
				classes: [ 'wrapper-sort-handle' ]
			} ).$element );
		}

		this.$element.append( this.layout.$element );
		this.$element.append( this.$overlay );
		this.setupSubitems();

		this.$element.addClass( 'ext-forms-form-editor-element-wrapper' );
		if ( this.isLayout ) {
			this.$element.addClass( 'wrapper-for-layout' );
		}
		this.$element.attr( 'id', this.identifier );

		OO.ui.getDefaultOverlay().append( this.popup.$element );

		this.$overlay.click( function() {
			this.group.openItemOptions( this.identifier, this );
		}.bind( this ) );
	};

	OO.inheritClass( mw.ext.forms.widget.formElement.FormElementWrapper, OO.ui.Widget );
	OO.mixinClass( mw.ext.forms.widget.formElement.FormElementWrapper, OO.ui.mixin.PopupElement );

	mw.ext.forms.widget.formElement.FormElementWrapper.prototype.getNameAndToolsBadge = function() {

		this.removeButton = new OO.ui.ButtonWidget( {
			icon: 'trash',
			title: 'Remove element',
			framed: false,
			flags: [ 'destructive', 'primary' ]
		} );
		this.removeButton.connect( this, {
			click: function() {
				this.group.removeElement( this.identifier );
			}
		} );

		var items = [
			this.removeButton
		];

		if ( this.isLayout ) {
			items.push( new OO.ui.ButtonWidget( {
				icon: 'draggable',
				title: 'Drag to reorder',
				framed: false,
				classes: [ 'wrapper-sort-handle', 'handle-in-tool' ]
			} ) );
		}

		return new OO.ui.HorizontalLayout( {
			items: items,
			classes: [ 'name-badge' ]
		} );
	};

	mw.ext.forms.widget.formElement.FormElementWrapper.prototype.getPopup = function() {
		return this.popup;
	};

	mw.ext.forms.widget.formElement.FormElementWrapper.prototype.getOptionsPanel = function() {
		return this.optionsPanel;
	};

	mw.ext.forms.widget.formElement.FormElementWrapper.prototype.openOptions = function() {
		this.popup.$body.html( this.optionsPanel.$element );
		this.popup.toggle( true );
	};

	mw.ext.forms.widget.formElement.FormElementWrapper.prototype.setupSubitems = function() {
		var subitemConfig = this.formDescriptor.getElement().getSubitemConfig();
		if ( !subitemConfig ) {
			return;
		}
		var accept = subitemConfig.accepts || null;
		if ( accept === null ) {
			return;
		}
		if ( !$.isArray( accept ) ) {
			accept = [accept];
		}

		this.render.$group.addClass( 'subitem-sortable' );
		this.render.$group.sortable( {
			activate: function () {
				this.popup.toggle( false );
			}.bind( this ),
			receive: function (event, ui) {
				this.group.subitemHandledSort( event.timeStamp );
				var item = ui.item;
				if (
					item.hasClass( 'ext-forms-form-editor-element-wrapper' ) &&
					item.attr( 'id' ).startsWith ( 'form_element' )
				) {
					return this.insertSubitemFromExisting( item, accept );
				}

				return this.insertSubitemFromNew( item, accept );

			}.bind( this ),
			connectWith: '#item-sortable-wrapper',
			handle: '.wrapper-sort-handle',
			placeholder: 'item-sortable-placeholder'
		} );
		this.formDescriptor.setSortableContainer( this.render.$group );
	};

	mw.ext.forms.widget.formElement.FormElementWrapper.prototype.insertSubitemFromExisting = function( item, accept ) {
		this.group.findElementByIdentifier( item.attr( 'id' ) ).done( function( element ) {
			var elementType = mw.ext.forms.widget.formElement.FormElementGroup.static.getElementType( element.getElement() ),
				key = element.elementName,
				accepted = false;

			for ( var i = 0; i < accept.length; i++ ) {
				if ( key === accept[i] || elementType === accept[i] ) {
					accepted = true;
					break;
				}
			}
			if ( !accepted ) {
				return;
			}

			this.formDescriptor.addSubitem( element.elementName, element.values );
			this.group.removeElement( element );
			item.remove();
		}.bind( this ) ).fail ( function() {
			item.remove();
		} );
	};

	mw.ext.forms.widget.formElement.FormElementWrapper.prototype.insertSubitemFromNew = function( item, accept ) {
		var selector = [];
		for (var i = 0; i < accept.length; i++) {
			selector.push( '.picker-element-item[data-type="' + accept[i] + '"]' );
			selector.push( '.picker-element-item[data-key="' + accept[i] + '"]' );
		}

		if ( !item.is( selector.join( ',' ) ) ) {
			item.remove();
			return;
		}

		var type = item.attr( 'data-key' );
		item.remove();

		this.formDescriptor.addSubitem( type );
	};

} )( mediaWiki, jQuery );
