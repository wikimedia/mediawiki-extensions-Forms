( function ( mw, $, undefined ) {
	mw.ext.forms.widget.ElementPicker = function( options, forceOpen ) {
		mw.ext.forms.widget.ElementPicker.super.call( this, {} );

		this.forceOpen = forceOpen;
		this.$trigger = this.makeTrigger();
		this.$trigger.on( 'click', function() {
			this.open();
		}.bind( this ) );
		this.$items = this.makeElements( options );

		this.$element
			.addClass( 'ext-forms-form-editor-element-picker' )
			.append( this.$trigger, this.$items );

		if ( forceOpen ) {
			this.open( true );
		} else {
			this.close( true );
		}
	};

	OO.inheritClass( mw.ext.forms.widget.ElementPicker , OO.ui.Widget );

	mw.ext.forms.widget.ElementPicker.prototype.makeTrigger = function() {
		var $el = $( '<div>' ).addClass( 'picker-trigger' ),
			icon = new OO.ui.IconWidget( { icon: 'outline' } ),
			label = new OO.ui.LabelWidget( { label: 'Element picker' } ),
			expandIcon = new OO.ui.IconWidget( { icon: 'expand' } );

		$el.append( icon.$element, label.$element, expandIcon.$element );

		return $el;
	};

	mw.ext.forms.widget.ElementPicker.prototype.makeElements = function( options ) {
		this.back = new OO.ui.ButtonWidget( {
			icon: 'close',
			title: 'Close',
			framed: false,
			classes: [ 'close-elements' ]
		} );

		var $el = $( '<div>' ).addClass( 'picker-elements' ),
			top = new OO.ui.HorizontalLayout( { items: [
				new OO.ui.LabelWidget( {
					label: 'Drag elements to the form below'
				} ),
				this.back
			] } ),
			items = new OO.ui.HorizontalLayout( {
				classes: [ 'element-picker-items-layout' ]
			} );

		this.back.connect( this, {
			click: 'close'
		} );
		$el.append( top.$element );

		var sortedGroups = Object.keys( options ).sort();
		for ( var i = 0; i < sortedGroups.length; i++ ) {
			var group = sortedGroups[i],
				groupData = options[group],
				layout = new OO.ui.FieldsetLayout( {
					icon: groupData.group.getIcon() || '',
					label: groupData.group.getDisplayText() || group
				} );

			for( var ii = 0; ii < groupData.items.length; ii++ ) {
				var item =  new mw.ext.forms.widget.ElementPickerItem ( {
					label: groupData.items[ii].label,
					icon: groupData.items[ii].icon,
					data: {
						key: groupData.items[ii].data,
						type: groupData.items[ii].type
					}
				} );
				layout.addItems( [ item ] );
			}

			items.addItems( [ layout ] );
		}

		$el.append( items.$element );
		return $el;
	};

	mw.ext.forms.widget.ElementPicker.prototype.dropDone = function() {
		if ( !this.forceOpen ) {
			this.close( true );
		}
	};

	mw.ext.forms.widget.ElementPicker.prototype.dragStart = function() {

	};

	mw.ext.forms.widget.ElementPicker.prototype.open = function( instant ) {
		instant = instant || false;
		this.$trigger.hide();
		if ( instant ) {
			this.$items.show();
		} else {
			this.$items.slideDown();
		}
	};

	mw.ext.forms.widget.ElementPicker.prototype.close = function( instant ) {
		instant = instant || false;
		if ( instant ) {
			this.$items.hide();
			this.$trigger.show();
		} else {
			this.$items.slideUp( 400, function () {
				this.$trigger.slideDown();
			}.bind( this ) );
		}
	};
} )( mediaWiki, jQuery );
