( function ( mw, $ ) {
	mw.ext.forms.widget.ElementPicker = function ( options ) {
		mw.ext.forms.widget.ElementPicker.super.call( this, {} );

		this.$items = this.makeElements( options );

		this.$element
			.addClass( 'ext-forms-form-editor-element-picker' )
			.append( this.$items );
	};

	OO.inheritClass( mw.ext.forms.widget.ElementPicker, OO.ui.Widget );

	mw.ext.forms.widget.ElementPicker.prototype.makeElements = function ( options ) {
		const sortedGroups = Object.keys( options ).sort();
		const pages = [];
		for ( let i = 0; i < sortedGroups.length; i++ ) {
			const group = sortedGroups[ i ],
				groupData = options[ group ];

			if ( group === 'layout' ) {
				// TODO: Layouts not working properly
				continue;
			}
			pages.push( this.makePage( group, groupData ) );
		}
		const booklet = new OO.ui.BookletLayout( {
			expanded: false,
			outlined: true
		} );
		booklet.addPages( pages );

		return $( '<div>' ).addClass( 'picker-elements' ).append( booklet.$element );
	};

	mw.ext.forms.widget.ElementPicker.prototype.dragStart = function () {

	};

	mw.ext.forms.widget.ElementPicker.prototype.makePage = function ( group, groupData ) {
		const page = new OO.ui.PageLayout( group, {
			expanded: false, classes: [ 'ext-forms-form-editor-element-picker-page' ]
		} );
		page.setupOutlineItem = function () {
			this.outlineItem.setLabel( groupData.group.getDisplayText() || group );
			this.outlineItem.setIcon( groupData.group.getIcon() || '' );
		};
		for ( let i = 0; i < groupData.items.length; i++ ) {
			const item = new mw.ext.forms.widget.ElementPickerItem( {
				label: groupData.items[ i ].label,
				icon: groupData.items[ i ].icon,
				data: {
					key: groupData.items[ i ].data,
					type: groupData.items[ i ].type
				}
			} );
			page.$element.append( item.$element );
		}
		return page;

	};
}( mediaWiki, jQuery ) );
