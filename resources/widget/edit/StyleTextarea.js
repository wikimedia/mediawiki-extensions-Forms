( function ( mw ) {
	mw.ext.forms.widget.edit.StyleTextarea = function ( cfg ) {
		this.form = cfg.form;

		mw.ext.forms.widget.edit.StyleTextarea.parent.call( this, cfg );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.StyleTextarea, OO.ui.MultilineTextInputWidget );

	mw.ext.forms.widget.edit.StyleTextarea.prototype.setValue = function ( value ) {
		if ( !value ) {
			return;
		}

		const localCSS = this.form.getItem( 'local_css' ).getValue();
		for ( const key in localCSS ) {
			if ( !localCSS.hasOwnProperty( key ) ) {
				continue;
			}
			value = value.replace( localCSS[ key ].css, '{{' + key + '}}' );
		}

		mw.ext.forms.widget.edit.StyleTextarea.parent.prototype.setValue.call( this, value );
	};

	mw.ext.forms.widget.edit.StyleTextarea.prototype.getValue = function () {
		let value = mw.ext.forms.widget.edit.StyleTextarea.parent.prototype.getValue.call( this );

		const localCSS = this.form.getItem( 'local_css' ).getValue();
		const regex = new RegExp( '\{\{(.*?)\}\}', 'g' ); // eslint-disable-line prefer-regex-literals, no-useless-escape
		for ( const key in localCSS ) {
			if ( !localCSS.hasOwnProperty( key ) ) {
				continue;
			}
			value = value.replace( regex, localCSS[ key ].css );
		}

		return value;
	};

}( mediaWiki ) );
