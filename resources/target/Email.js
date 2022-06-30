( function ( mw, $, undefined ) {
	mw.ext.forms.target.Email = function( form, items ) {
		mw.ext.forms.target.Email.parent.call( this, form, items );
	};

	OO.inheritClass( mw.ext.forms.target.Email, mw.ext.forms.target.FormTarget );

	mw.ext.forms.target.Email.prototype.getName = function() {
		return 'email';
	};

	mw.ext.forms.target.Email.prototype.getDisplayName = function() {
		return mw.message( 'forms-form-editor-prop-targettypeemail' ).text();
	};

	mw.ext.forms.target.Email.prototype.getAdditionalFields = function() {
		var emailEntries = [];
		for ( var entry in mw.config.get( 'formsEmailTargets') ) {
			var item =  {
				data: mw.config.get( 'formsEmailTargets')[ entry ],
				label: mw.config.get( 'formsEmailTargets')[ entry ]
			};
			emailEntries.push( item );
		}

		return [
			{
				type: 'dropdown',
				name: 'target.receivers',
				required: true,
				label: mw.message( 'forms-form-editor-prop-target-email-receiver' ).text(),
				options: emailEntries
			},
			{
				type: 'textarea',
				name: 'target.subject',
				required: true,
				label: mw.message( 'forms-form-editor-prop-target-email-subject' ).text(),
				rows: 3
			},
			{
				type: 'textarea',
				name: 'target.body',
				required: true,
				label: mw.message( 'forms-form-editor-prop-target-email-body' ).text(),
				rows: 10
			}
		];
 	};

	mw.ext.forms.target.Email.prototype.getValue = function() {
		return {
			type: this.getName(),
			receivers: this.items['target.receivers'].getValue(),
			subject: this.items['target.subject'].getValue(),
			body: this.items['target.body'].getValue()
		}
	};

	mw.ext.forms.target.Email.prototype.setValue = function( value ) {
		if ( value.hasOwnProperty( 'receivers' ) && this.items.hasOwnProperty( 'target.receivers' ) ) {
			this.items['target.receivers'].setValue( value.receivers );
		}
		if ( value.hasOwnProperty( 'subject ' ) && this.items.hasOwnProperty( 'target.subject' ) ) {
			this.items[ 'target.subject' ].setValue( value.subject );
		}
		if ( value.hasOwnProperty( 'body ' ) && this.items.hasOwnProperty( 'target.body' ) ) {
			this.items[ 'target.body' ].setValue( value.body );
		}
	};

	mw.ext.forms.registry.Target.register( 'email', mw.ext.forms.target.Email );
} )( mediaWiki, jQuery );
