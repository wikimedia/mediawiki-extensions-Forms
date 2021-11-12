( function ( mw, $, undefined ) {
	mw.ext.forms.widget.view.SectionLabel = function( cfg ) {
		cfg.label = cfg.title || '';

		mw.ext.forms.widget.view.SectionLabel.parent.call( this, cfg );

		this.$element.addClass( 'forms-section' );
	};

	OO.inheritClass( mw.ext.forms.widget.view.SectionLabel, OO.ui.LabelWidget );

} )( mediaWiki, jQuery );