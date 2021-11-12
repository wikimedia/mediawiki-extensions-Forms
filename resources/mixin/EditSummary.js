( function ( mw, $, undefined ) {
	mw.ext.forms.mixin.EditSummary = function() {
		if ( !this.enableEditSummary ) {
			return;
		}
		var placeholder = mw.message( 'forms-edit-summary-placeholder' ).text();

		if ( typeof this.enableEditSummary === 'object' ) {
			if ( !this.enableEditSummary.hasOwnProperty( 'showSummaryInput' ) ||
				this.enableEditSummary.showSummaryInput === false ) {
				return this.enableEditSummary = false;
			}
			if ( this.enableEditSummary.hasOwnProperty( 'showOnCreate' ) &&
				this.enableEditSummary.showOnCreate === false &&
				this.action === 'create' ) {
				return this.enableEditSummary = false;
			}
			if ( this.enableEditSummary.hasOwnProperty( 'placeholder' ) ) {
				placeholder = this.enableEditSummary.placeholder;
			}

			this.enableEditSummary = true;
		}

		this.summaryInput = new mw.ext.forms.widget.DynamicLabelTextInputWidget( {
			maxLength: 255,
			placeholder: placeholder
		} );

		this.summaryInput.$element.insertBefore( this.buttonsLayout.$element.find( '#formSubmit' ) );
	};

	OO.initClass( mw.ext.forms.mixin.EditSummary );

	// TODO: Move to OOJSPlus
	// https://phabricator.wikimedia.org/diffusion/GOJU/browse/master/demos/classes/DynamicLabelTextInputWidget.js
	mw.ext.forms.widget.DynamicLabelTextInputWidget = function ( config ) {
		this.maxLength = config.maxLength || 255;
		// Configuration initialization
		config = $.extend( { getLabelText: function () {} }, config );
		// Parent constructor
		mw.ext.forms.widget.DynamicLabelTextInputWidget.parent.call( this, config );
		// Events
		this.connect( this, {
			change: 'onChange'
		} );
		// Initialization
		this.setLabel( this.getLabelText( this.getValue() ) );
	};
	OO.inheritClass( mw.ext.forms.widget.DynamicLabelTextInputWidget, OO.ui.TextInputWidget );

	mw.ext.forms.widget.DynamicLabelTextInputWidget.prototype.onChange = function ( value ) {
		this.setLabel( this.getLabelText( value ) );
	};

	mw.ext.forms.widget.DynamicLabelTextInputWidget.prototype.getLabelText = function ( value ) {
		return String( this.maxLength - value.length );
	};

} )( mediaWiki, jQuery );