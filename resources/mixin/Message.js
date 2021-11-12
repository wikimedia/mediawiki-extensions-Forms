( function ( mw, $, d ) {
	mw.ext.forms.mixin.Message = function() {
		this.addMessageContainer();
	};

	OO.initClass( mw.ext.forms.mixin.Message );

	mw.ext.forms.mixin.Message.prototype.addMessageContainer = function() {
		this.$messageContainer = $( '<div>' ).addClass( 'mw-ext-forms-message-cnt' );
		this.messageLabel = new OO.ui.LabelWidget();
		this.ackMessageButton = new OO.ui.ButtonWidget( {
			title: mw.message( 'forms-message-ack-button-tooltip' ).text(),
			framed: false,
			icon: 'close'
		} );
		this.ackMessageButton.on( 'click', this.clearMessage.bind( this ) );
		this.$messageContainer.append(
			this.messageLabel.$element,
			this.ackMessageButton.$element
		);
		this.$element.append( this.$messageContainer );
		this.$messageContainer.hide();
	};

	mw.ext.forms.mixin.Message.prototype.errorMessage = function( message, autoHide ) {
		this.showMessage( 'error', message, autoHide );
	};

	mw.ext.forms.mixin.Message.prototype.successMessage = function( message, autoHide ) {
		this.showMessage( 'ok', message, autoHide );
	};

	mw.ext.forms.mixin.Message.prototype.infoMessage = function( message, autoHide ) {
		this.showMessage( 'info', message, autoHide );
	};

	mw.ext.forms.mixin.Message.prototype.showMessage = function( type, message, autoHide ) {
		if ( this.$element.find( '.mw-ext-forms-message-cnt' ).length === 0 ) {
			this.addMessageContainer();
		}
		this.clearMessage();
		this.messageLabel.setLabel( message );
		this.$messageContainer.addClass( 'type-' + type );
		this.$messageContainer.slideDown( 500 );
		if ( autoHide ) {
			// Make message disappear on its own after a while
			this.messageTimeout = setTimeout( this.clearMessage.bind( this ), 5000 );
		}
	};

	mw.ext.forms.mixin.Message.prototype.clearMessage = function() {
		if ( this.messageTimeout ) {
			clearTimeout( this.messageTimeout );
		}
		this.messageLabel.setLabel();
		this.$messageContainer.removeClass();
		this.$messageContainer.addClass( 'mw-ext-forms-message-cnt' );
		this.$messageContainer.hide();
	};

} )( mediaWiki, jQuery, document );