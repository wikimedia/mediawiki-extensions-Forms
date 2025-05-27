mw.ext.forms.dialog = mw.ext.forms.dialog || {};
mw.ext.forms.dialog.SaveFormDefinitionDialog = function ( cfg ) {
	mw.ext.forms.dialog.SaveFormDefinitionDialog.parent.call( this, cfg );
	this.formData = cfg.formData || {};
	this.itemsForm = cfg.itemsForm || null;
	this.propertiesForm = cfg.propertiesForm || null;
	this.targetPage = cfg.targetPage || null;
};

OO.inheritClass( mw.ext.forms.dialog.SaveFormDefinitionDialog, OO.ui.ProcessDialog );

mw.ext.forms.dialog.SaveFormDefinitionDialog.static.name = 'saveFormDefinitionDialog';
mw.ext.forms.dialog.SaveFormDefinitionDialog.static.title = mw.message( 'forms-editor-toolbar-save-label' ).text();
mw.ext.forms.dialog.SaveFormDefinitionDialog.static.padded = true;
mw.ext.forms.dialog.SaveFormDefinitionDialog.static.actions = [
	{ action: 'save', label: mw.message( 'forms-editor-toolbar-done' ).text(), flags: [ 'primary', 'progressive' ] },
	{ action: 'cancel', label: mw.message( 'forms-editor-toolbar-cancel' ).text(), flags: 'safe' }
];

mw.ext.forms.dialog.SaveFormDefinitionDialog.prototype.initialize = function () {
	mw.ext.forms.dialog.SaveFormDefinitionDialog.super.prototype.initialize.call( this );

	this.content = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );
	this.$body.append( this.content.$element );

	this.summary = new OO.ui.TextInputWidget( {
		placeholder: mw.message( 'forms-form-editor-form-props-summary-placeholder' ).text()
	} );
	this.content.$element.append( new OO.ui.FieldLayout( this.summary, {
		label: mw.message( 'forms-form-editor-form-props-summary-label' ).text(),
		align: 'top'
	} ).$element );
};

mw.ext.forms.dialog.SaveFormDefinitionDialog.prototype.getActionProcess = function ( action ) {
	return mw.ext.forms.dialog.SaveFormDefinitionDialog.parent.prototype.getActionProcess.call( this, action ).next(
		function () {
			if ( action === 'save' ) {

				this.pushPending();
				const mainPromise = $.Deferred(),
					propertiesPromise = $.Deferred(),
					itemsPromise = $.Deferred();
				this.propertiesForm.connect( this, {
					dataSubmitted: function ( data ) {
						propertiesPromise.resolve( data );
					},
					validationFailed: function () {
						mainPromise.reject();
					}
				} );
				this.propertiesForm.submit();

				this.itemsForm.connect( this, {
					dataSubmitted: function ( data ) {
						itemsPromise.resolve( data );
					},
					validationFailed: function () {
						mainPromise.reject();
					}
				} );
				this.itemsForm.submit();

				$.when( propertiesPromise, itemsPromise ).done( ( properties, items ) => {

					const data = Object.assign( {}, properties, items ),
						summary = this.summary.getValue();
					const apiData = {
						action: 'forms-form-submit',
						form: '_formEditor',
						target: JSON.stringify( {
							type: 'form-definition',
							title: this.targetPage
						} ),
						data: JSON.stringify( data ),
						token: mw.user.tokens.get( 'csrfToken' )
					};
					if ( summary ) {
						apiData.summary = summary;
					}
					const api = new mw.Api();
					api.post( apiData ).done( ( data ) => { // eslint-disable-line no-shadow
						if ( data.success === 1 ) {
							mainPromise.resolve( this.close( { action: 'save', data: data } ) );
						} else {
							mainPromise.reject();
						}
					} ).fail( () => {
						this.popPending();
						mainPromise.reject();
					} );
				} );

				return mainPromise.promise();
			}
			if ( action === 'cancel' ) {
				this.close( { action: 'cancel' } );
			}
		}, this
	);
};
