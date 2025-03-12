mw.ext.forms.dialog = mw.ext.forms.dialog || {};
mw.ext.forms.dialog.FormPropertiesDialog = function ( cfg ) {
	mw.ext.forms.dialog.FormPropertiesDialog.parent.call( this, cfg );
	this.form = cfg.form;
};

OO.inheritClass( mw.ext.forms.dialog.FormPropertiesDialog, OO.ui.ProcessDialog );

mw.ext.forms.dialog.FormPropertiesDialog.static.name = 'formPropertiesDialog';
mw.ext.forms.dialog.FormPropertiesDialog.static.title = mw.message( 'forms-form-editor-form-props-label' ).text();
mw.ext.forms.dialog.FormPropertiesDialog.static.size = 'larger';
mw.ext.forms.dialog.FormPropertiesDialog.static.padded = true;
mw.ext.forms.dialog.FormPropertiesDialog.static.actions = [
	{ action: 'save', label: mw.message( 'forms-editor-toolbar-done' ).text(), flags: [ 'primary', 'progressive' ] },
	{ action: 'cancel', label: mw.message( 'forms-editor-toolbar-cancel' ).text(), flags: 'safe' }
];

mw.ext.forms.dialog.FormPropertiesDialog.prototype.initialize = function () {
	mw.ext.forms.dialog.FormPropertiesDialog.super.prototype.initialize.call( this );

	this.content = new OO.ui.PanelLayout( {
		padded: false,
		expanded: false
	} );
	this.$body.append( this.content.$element );

	this.form.connect( this, {
		layoutChange: function () {
			this.updateSize();
		}
	} );
	this.form.$element.find( '.forms-form-layout-inner' ).removeClass( 'oo-ui-panelLayout-framed' );
	this.form.$element.addClass( 'nopadding' );
	this.content.$element.append( this.form.$element );
};

mw.ext.forms.dialog.FormPropertiesDialog.prototype.getActionProcess = function ( action ) {
	return mw.ext.forms.dialog.FormPropertiesDialog.parent.prototype.getActionProcess.call( this, action ).next(
		function () {
			if ( action === 'save' ) {
				this.pushPending();
				const dfd = $.Deferred();
				this.form.connect( this, {
					dataSubmitted: function ( data ) { // eslint-disable-line no-unused-vars
						dfd.resolve( this.close() );
					},
					validationFailed: function () {
						this.popPending();
						dfd.resolve();
					}
				} );
				this.form.submit();

				return dfd.promise();
			}
			if ( action === 'cancel' ) {
				this.close();
			}
		}, this
	);
};
