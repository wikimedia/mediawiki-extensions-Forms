( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.OptionView = function( cfg ) {
		mw.ext.forms.widget.view.OptionView.parent.call( this, cfg );
	};

	OO.inheritClass( mw.ext.forms.widget.view.OptionView, OO.ui.LabelWidget );

	mw.ext.forms.widget.view.OptionView.prototype.setValue = function( value, config, widgetConfig ) {
		if ( widgetConfig.hasOwnProperty( 'options' ) ) {
			for( var i = 0; i < widgetConfig.options.length; i++ ) {
				if ( widgetConfig.options[i].data === value ) {
					return this.setLabel( widgetConfig.options[i].label );
				}
			}
		}
		this.setLabel( value );
	};

} )( mediaWiki, jQuery );