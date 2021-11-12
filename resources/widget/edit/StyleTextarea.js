( function ( mw, $, undefined ) {
	mw.ext.forms.widget.edit.StyleTextarea = function( cfg ) {
		this.form = cfg.form;

		mw.ext.forms.widget.edit.StyleTextarea.parent.call( this, cfg );
	};

	OO.inheritClass( mw.ext.forms.widget.edit.StyleTextarea, OO.ui.MultilineTextInputWidget );

	mw.ext.forms.widget.edit.StyleTextarea.prototype.setValue = function( value ) {
		if ( !value ) {
			return;
		}

		var localCSS = this.form.getItem( 'local_css' ).getValue();
		for ( var key in localCSS ) {
			if ( !localCSS.hasOwnProperty( key ) ) {
				continue;
			}
			value = value.replace( localCSS[key].css, '{{' + key + '}}' );
		}

		mw.ext.forms.widget.edit.StyleTextarea.parent.prototype.setValue.call( this, value );
	};

	mw.ext.forms.widget.edit.StyleTextarea.prototype.getValue = function() {
		var value =  mw.ext.forms.widget.edit.StyleTextarea.parent.prototype.getValue.call( this );

		var localCSS = this.form.getItem( 'local_css' ).getValue();
		var regex = new RegExp( '\{\{(.*?)\}\}', 'g' );
		for ( var key in localCSS ) {
			if ( !localCSS.hasOwnProperty( key ) ) {
				continue;
			}
			value = value.replace( regex, localCSS[key].css );
		}

		return value;
	};

} )( mediaWiki, jQuery );
