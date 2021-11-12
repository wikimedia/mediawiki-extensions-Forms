( function ( mw, $, d ) {
	mw.ext.forms.mixin.FormLoadingAlert = function( hidden ) {
		this.$loadingAlert = $( '<div>' ).addClass( 'mw-ext-forms-form-loading-alert' )
			.append(
				new OO.ui.LabelWidget( {
					label: mw.message( 'forms-form-loading-label' ).text()
				} ).$element
			);

		this.$element.append( this.$loadingAlert );
		this.setLoadingVisibility( !hidden );
	};

	OO.initClass( mw.ext.forms.mixin.FormLoadingAlert );

	mw.ext.forms.mixin.FormLoadingAlert.prototype.setLoadingVisibility = function( show ) {
		this.$loadingAlert.hide();
		if ( show ) {
			this.$loadingAlert.show();
		}
	};

} )( mediaWiki, jQuery, document );