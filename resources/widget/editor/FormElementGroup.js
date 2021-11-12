( function ( mw, $, undefined ) {
	mw.ext.forms.widget.formElement.FormElementGroup = function( cfg ) {
		mw.ext.forms.widget.formElement.FormElementGroup.parent.call( this, cfg );

		this.$input.remove();
		this.form = cfg.form;
		this.items = {};
		this.dropAllowed = true;

		this.createAreas();

		this.$element.addClass( 'ext-forms-form-element-group' );
	};

	OO.inheritClass( mw.ext.forms.widget.formElement.FormElementGroup, OO.ui.InputWidget );

	mw.ext.forms.widget.formElement.FormElementGroup.static.getElementPickerItems = function() {
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

	mw.ext.forms.widget.formElement.FormElementGroup.static.getElementPickerOptions = function() {
		var grouped = mw.ext.forms.widget.formElement.FormElementGroup.static.getElementPickerItems(),
			options = [];
		for( var group in grouped ) {
			options.push( {
				optgroup: grouped[group].group.getDisplayText(),
			} );
			for( var i = 0; i < grouped[group].items.length; i++ ) {
				options.push( {
					data: grouped[group].items[i].data,
					label: grouped[group].items[i].label,
				} );
			}
		}

		return options;
	};

	mw.ext.forms.widget.formElement.FormElementGroup.static.getElementType = function( element ) {
		if ( element instanceof mw.ext.forms.formElement.FormLayoutElement ) {
			return 'layout';
		}
		if ( element instanceof mw.ext.forms.formElement.InputFormElement ) {
			return 'input';
		}

		return 'static';
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.createAreas = function() {
		this.$sortable = $( '<ul>' ).attr( 'id', 'item-sortable-wrapper' );
		this.$sortable.sortable( {
			connectWith: '.subitem-sortable',
			receive: function( e, ui ) {
				// This is fired before the event the nested sortable,
				// we wait a bit until we see if nested sortable will handle it
				// if not then we handle it
				setTimeout( function() {
					if ( this.handledSortTs === e.timeStamp ) {
						this.handledSortTs = null;
						return;
					}
					if ( !this.dropAllowed ) {
						return;
					}

					if (
						ui.item.hasClass( 'ext-forms-form-editor-element-wrapper' ) &&
						ui.item.attr( 'id' ).startsWith ( 'form_element' )
					) {
						this.findElementByIdentifier( ui.item.attr( 'id' ) ).done( function( element ) {
							this.addExistingFromElement( element );
							ui.item.remove();
						}.bind( this ) ).fail( function() {
							ui.item.remove();
						} );
					} else {
						this.addNewFromDraggable( ui.item, true );
						this.elementPicker.dropDone();
					}
					this.dropAllowed = false;
					setTimeout( function() {
						// There are all kinds of bubbling happening,
						// Could not control them otherwise
						this.dropAllowed = true;
					}.bind( this ), 1000 );

				}.bind( this ), 20 );
			}.bind( this ),
			activate: function() {
				this.$previewArea.addClass( 'active-drag' );
			}.bind( this ),
			over: function() {
				this.$previewArea.find( '.empty-help-placeholder' ).hide();
				this.$previewArea.addClass( 'drop-valid' );
			}.bind( this ),
			out: function() {
				this.$previewArea.find( '.empty-help-placeholder' ).show();
				this.$previewArea.removeClass( 'drop-valid' );
			}.bind( this ),
			deactivate: function() {
				this.$previewArea.removeClass( 'active-drag' ).removeClass( 'drop-valid' );
			}.bind( this ),
			placeholder: 'item-sortable-placeholder',
			handle: ".wrapper-sort-handle"
		} ).disableSelection();

		this.$previewArea = $( '<div>' )
			.addClass( 'editor-element' )
			.addClass( 'preview-area' )
			.append( this.$sortable );

		this.setHelpPlaceholder();

		this.elementPicker = new mw.ext.forms.widget.ElementPicker(
			mw.ext.forms.widget.formElement.FormElementGroup.static.getElementPickerItems(), true
		);
		this.$element.append(  this.elementPicker.$element, this.$previewArea );
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.addNewFromDraggable = function( $draggable, removeDraggable ) {
		removeDraggable = removeDraggable || false;
		var key = $draggable.attr( 'data-key' );
		if ( !key ) {
			return;
		}

		if ( removeDraggable ) {
			this.$sortable.find( '.picker-element-item[data-key=' + key + ']' ).attr( 'id', 'drop-target' );
			this.addElement( key, {}, null, 'drop-target' );
			this.$sortable.find( '#drop-target' ).remove();
		} else {
			this.addElement( key );
		}
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.addExistingFromElement = function( element ) {
		var type = element.getElementName(),
			value = element.getValue();

		if ( this.isSubitem( element ) ) {
			element.getParentDescriptor().removeSubitem( element.getIdentifier() );
		} else {
			this.removeElement( element );
		}

		this.addElement( type, value, element.getIdentifier(), element.getIdentifier() );
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.findElementByIdentifier = function( identifier ) {
		var dfd = $.Deferred();
		this.doFindElement( identifier, this.items, dfd, 0 );
		return dfd.promise();
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.doFindElement = function( identifier, items, dfd, level ) {
		if ( items.hasOwnProperty( identifier ) ) {
			return dfd.resolve( items[identifier] );
		}
		for ( var id in items ) {
			if ( !items.hasOwnProperty( id ) ) {
				continue;
			}
			this.doFindElement( identifier, items[id].getSubitems(), dfd, level + 1 );
		}
		if ( level === 0 ) {
			dfd.reject();
		}
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.addElement = function( typeToAdd, values, identifier, addAfter ) {
		if ( !mw.ext.forms.registry.Type.registry.hasOwnProperty( typeToAdd ) ) {
			return;
		}
		var element = mw.ext.forms.registry.Type.lookup( typeToAdd );

		values = $.extend( {}, element.getDefaultValue(), values || {} );
		identifier = identifier || this.getNewIdentifier();
		var elementDescriptor = new mw.ext.forms.widget.formElement.FormEditorElementDescriptor( {
			identifier: identifier,
			elementName: typeToAdd,
			group: this,
			form: this.form,
			values: values
		} );

		elementDescriptor.connect( this, {
			propChange: 'reRenderItem',
			addSubitem: 'reRenderItem'
		} );

		this.renderItem( elementDescriptor, addAfter );

		return elementDescriptor;
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.reRenderItem = function( descriptor ) {
		var newDescriptor = this.renderItem( descriptor, null, true );
		this.$previewArea.find( '#' + descriptor.getIdentifier() ).replaceWith( newDescriptor.$element );
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.getNewIdentifier = function() {
		// This identifier is only used during the editing to identify the elements between
		// different panels, does not go into the actual form definition
		var identifier = null;
		do {
			identifier = this.form.getRandomName( 'form_element', Math.floor( Math.random() * (9999 - 100) + 100) );
		} while ( this.items.hasOwnProperty( identifier ) );

		return identifier;
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.clearElements = function() {
		for( var id in this.items ) {
			if ( !this.items.hasOwnProperty( id ) ) {
				continue;
			}
			this.removeElement( this.items[id] );
		}
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.removeElement = function( item ) {
		if ( typeof item === 'string' ) {
			this.findElementByIdentifier( item ).done( function( element ) {
				this.removeElement( element );
			}.bind( this ) );
			return;
		}

		if ( item.getParentDescriptor() !== null ) {
			item.getParentDescriptor().removeSubitem( item.getIdentifier() );
		} else {
			delete( this.items[item.getIdentifier()] );
		}

		this.$sortable.children( '#' + item.getIdentifier() ).remove();

		if ( Object.keys( this.items ).length === 0 ) {
			this.setHelpPlaceholder();
		}
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.isSubitem = function( item ) {
		return item.getParentDescriptor() !== null;
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.parentInGroup = function( item ) {
		item = $.extend( {}, item );
		while( item.getParentDescriptor() !== null ) {
			item = item.getParentDescriptor();
		}
		return this.items.hasOwnProperty( item.getIdentifier() );
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.renderAllItems = function() {
		this.$sortable.children().remove();
		var itemsToRender = this.getOrderedItems();
		if ( $.isEmptyObject( itemsToRender ) ) {
			if ( Object.keys( this.items ).length === 0 ) {
				this.setHelpPlaceholder();
			}
			return;
		}
		this.recursiveRender( $.extend( {}, {}, itemsToRender ) );
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.recursiveRender = function( items ) {
		console.log( Object.keys( items ).length );
		if ( $.isEmptyObject( items ) ) {
			return;
		}
		var keyToProcess = Object.keys( items ).shift();

		this.renderItem( items[keyToProcess] );
		delete( items[keyToProcess] );
		this.recursiveRender( items );
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.renderItem = function( item, afterId, noAppend ) {
		var toParse = item.getValueForPreview(),
			layout = new OO.ui.FieldsetLayout(),
			name = toParse.name + '-wrapper';

		afterId = afterId || false;

		if ( this.$previewArea.find( '.empty-help-placeholder' ).length ) {
			this.$previewArea.find( '.empty-help-placeholder' ).remove();
		}

		var wrapper = {
			name: name,
			type: 'form_element_wrapper',
			noLayout: true,
			original: toParse,
			identifier: item.getIdentifier(),
			formDescriptor: item,
			group: this
		};

		var rendered = this.form.parseItems( [ wrapper ], layout, true );

		this.items[item.getIdentifier()] = item;
		if ( !noAppend ) {
			if ( afterId && this.$sortable.find( '#' + afterId ).length > 0 ) {
				layout.$group.children().insertAfter( this.$sortable.find( '#' + afterId ) );
			} else {
				this.$sortable.append( layout.$group.children() );
			}
		}

		return rendered[Object.keys( rendered )[0]];
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.setValue = function( value ) {
		if ( !value ) {
			return;
		}
		for( var i = 0; i < value.length; i++ ) {
			var element = value[i];
			if ( !element.hasOwnProperty( 'type' ) ) {
				continue;
			}
			var descriptor = this.addElement( element.type );
			descriptor.setValue( element );
		}
		this.renderAllItems();
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.getValue = function() {
		var value = [];
		var ordered = this.getOrderedItems();
		for( var item in ordered ) {
			if ( !ordered.hasOwnProperty( item ) ) {
				continue;
			}
			value.push( ordered[item].getValue() );
		}

		return value;
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.openItemOptions = function( id, wrapper ) {
		this.findElementByIdentifier( id ).done( function( element ) {
			wrapper.optionsPanel.clearItems();
			element.renderOptions( wrapper.optionsPanel, element.getValue() );
			wrapper.openOptions();
		}.bind( this ) );

	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.getValidity = function() {
		var toValidate = $.extend( {}, this.items );
		return this.form.validateForm( toValidate );
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.getOrderedItems = function( items, $sortable ) {
		items = items || this.items;
		$sortable = $sortable || this.$sortable;

		if ( $sortable.children().length === Object.keys( items ).length ) {
			var sortedIds = [], sortedItems = {};
			$sortable.children().each( function() {
				sortedIds.push( $( this ).attr( 'id' ) );
			} );

			for ( var i = 0; i < sortedIds.length; i++ ) {
				if ( !items.hasOwnProperty( sortedIds[i] ) ) {
					continue;
				}
				sortedItems[sortedIds[i]] = items[sortedIds[i]];
			}

			return sortedItems;
		}

		return $.extend( true, {}, {}, items );
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.setHelpPlaceholder = function() {
		this.$previewArea.prepend(
			$( '<div>' ).addClass( 'empty-help-placeholder' ).text( 'Drop elements from the Element picker here to add them to the form' )
		);
	};

	mw.ext.forms.widget.formElement.FormElementGroup.prototype.subitemHandledSort = function( ts ) {
		this.handledSortTs = ts;
	};


} )( mediaWiki, jQuery );
