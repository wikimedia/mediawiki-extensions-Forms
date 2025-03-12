( function ( mw, $ ) {
	mw.ext.forms.widget.ElementPickerItem = function ( cfg ) {
		mw.ext.forms.widget.ElementPickerItem.parent.call( this, cfg );

		OO.ui.mixin.IconElement.call( this, cfg );
		OO.ui.mixin.LabelElement.call( this, cfg );
		OO.ui.mixin.TitledElement.call( this, Object.assign( {
			$titled: this.$element
		}, cfg ) );

		this.innerLayout = new OO.ui.HorizontalLayout();
		if ( cfg.icon ) {
			// Weird, but cannot style without it due to flex
			this.$label.addClass( 'with-icon' );
		}
		this.innerLayout.$element.append( this.$icon, this.$label );
		this.$element.append( this.innerLayout.$element );

		this.$element.draggable( {
			start: function () {
				this.emit( 'dragstart', $( this ) );
			}.bind( this ),
			stop: function () {
				this.emit( 'dragend', $( this ) );
			}.bind( this ),
			revert: 'invalid',
			helper: 'clone',
			connectToSortable: '#item-sortable-wrapper'
		} );

		const data = this.getData();
		this.$element.attr( 'data-key', data.key );
		this.$element.attr( 'data-type', data.type );

		this.$element.addClass( 'picker-element-item' );
	};

	OO.inheritClass( mw.ext.forms.widget.ElementPickerItem, OO.ui.Widget );
	OO.mixinClass( mw.ext.forms.widget.ElementPickerItem, OO.ui.mixin.IconElement );
	OO.mixinClass( mw.ext.forms.widget.ElementPickerItem, OO.ui.mixin.LabelElement );
	OO.mixinClass( mw.ext.forms.widget.ElementPickerItem, OO.ui.mixin.TitledElement );

}( mediaWiki, jQuery ) );
