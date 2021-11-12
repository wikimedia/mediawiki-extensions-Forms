( function ( mw, $, undefined ) {
	mw.ext.forms.widget.edit.SelectFileInput = function( cfg ) {
		this.fileWidget = new OO.ui.SelectFileWidget( $.extend( {
			accept: [
				'image/png',
				'image/jpeg'
			],
			$tabIndexed: $( '<a class="oo-ui-buttonElement-button" role="button" tabindex="0" aria-disabled="false" rel="nofollow"><span class="oo-ui-iconElement-icon oo-ui-icon-upload"></span><span class="oo-ui-labelElement-label">Select a file</span><span class="oo-ui-indicatorElement-indicator oo-ui-indicatorElement-noIndicator"></span><input title="" class="oo-ui-inputWidget-input" type="file" tabindex="-1" accept="image/png, image/jpeg"></a>' ),
			showDropTarget: true
		}, cfg ) );
		mw.ext.forms.widget.edit.SelectFileInput.parent.call( this, cfg );
		this.$input.remove();
		this.$element.append( this.fileWidget.$element );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.SelectFileInput, OO.ui.InputWidget );

	mw.ext.forms.widget.edit.SelectFileInput.prototype.setValue = function( value ) {
		if ( !value ) {
			return this.fileWidget.setValue( null );
		}
		this.toFile( value ).then( function( file ) {
			this.fileWidget.setValue( file );
		}.bind( this ) );
	};

	mw.ext.forms.widget.edit.SelectFileInput.prototype.getValue = function( raw ) {
		let dfd = $.Deferred();
		let value = this.fileWidget.getValue();
		if ( !value || raw ) {
			dfd.resolve( value );
			return dfd.promise();
		}

		let file = value;
		let reader = new FileReader();
		reader.readAsDataURL( file );
		reader.onload = function( e ) {
			dfd.resolve( {
				filename: file.name,
				type: file.type,
				base64: reader.result
			} );
		}.bind( this );
		return dfd.promise();
	};

	mw.ext.forms.widget.edit.SelectFileInput.prototype.toFile = function( value ) {
		return ( fetch( value.base64 )
				.then(function( res ){ return res.arrayBuffer(); } )
				.then(function( buf ){
					return new File([buf], value.filename, { type: value.type } );
				} )
		);
	};

} )( mediaWiki, jQuery );