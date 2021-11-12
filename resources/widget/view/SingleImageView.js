( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.SingleImageView = function( cfg ) {
		mw.ext.forms.widget.view.SingleImageView.parent.call( this, cfg );

		this.$element.addClass( 'ext-forms-single-image-view-widget' );
		this.$image = $( '<div>' ).addClass( 'inner-image' );
		this.$element.append( this.$image );
	};

	OO.inheritClass( mw.ext.forms.widget.view.SingleImageView, OO.ui.Widget );

	mw.ext.forms.widget.view.SingleImageView.static.tagName = 'div';

	mw.ext.forms.widget.view.SingleImageView.prototype.setValue = function( value ) {
		if ( value ) {
			this.$image.css( {
				'background-image': "url( '" + value.base64 + "' )"
			} );
		} else {
			this.$image.removeAttr( 'style' );
			this.$image.addClass( 'no-image' );
		}
	};

} )( mediaWiki, jQuery );