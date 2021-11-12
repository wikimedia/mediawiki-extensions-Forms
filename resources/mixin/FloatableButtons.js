( function ( mw, $, undefined ) {
	mw.ext.forms.mixin.FloatableButtons = function() {

		this.submitButton = new OO.ui.ButtonWidget( {
			label: mw.message( 'forms-form-submit-label' ).text(),
			flags: [
				"progressive",
				"primary"
			],
			id: 'formSubmit'
		} );
		this.resetButton = new OO.ui.ButtonWidget( {
			label: mw.message( 'forms-form-reset-label' ).text(),
			framed: false
		} );

		this.cancelButton = new OO.ui.ButtonWidget( {
			label: mw.message( 'forms-form-cancel-label' ).text(),
			framed: false
		} );

		var classes = [ 'ext-mw-forms-form-buttons' ];
		if ( this.buttonsFloat ) {
			classes.push( 'float-buttons' );
		}

		var items = [];
		if ( this.buttonsToShow.indexOf( 'submit' ) !== -1 ) {
			items.push( this.submitButton );
		}
		if ( this.buttonsToShow.indexOf( 'reset' ) !== -1 ) {
			items.push( this.resetButton );
		}
		if ( this.buttonsToShow.indexOf( 'cancel' ) !== -1 ) {
			items.push( this.cancelButton );
		}
		this.buttonsLayout = new OO.ui.HorizontalLayout( {
			classes: classes,
			items: items
		} );
		this.layout.addItems( this.buttonsLayout );

		this.cancelButton.setDisabled( this.action !== 'edit' );
	};

	OO.initClass( mw.ext.forms.mixin.FloatableButtons );

} )( mediaWiki, jQuery );