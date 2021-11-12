( function ( mw, $, d ) {
	mw.ext.forms.mixin.BookletLayoutParser = function() {};

	OO.initClass( mw.ext.forms.mixin.BookletLayoutParser );

	mw.ext.forms.mixin.BookletLayoutParser.prototype.parseBooklet = function( pages, booklet ) {
		for( var i = 0; i < pages.length; i++ ) {
			var page = pages[i];
			if ( this.shouldShow( page.showOn ) === false ) {
				continue;
			}

			var pageLayout = new mw.ext.forms.widget.BookletPageLayout( page, this );

			booklet.addPages( [ pageLayout ] );
		}
	};

	mw.ext.forms.widget.BookletPageLayout = function( page, parser ) {
		var contLayout = new OO.ui.FieldsetLayout();
		this.page = page;

		mw.ext.forms.widget.BookletPageLayout.parent.call( this, page.name, {
			expanded: false,
			padded: true
		} );
		this.$element.append( contLayout.$element );
		parser.parseItems( page.items, contLayout );
	};

	OO.inheritClass( mw.ext.forms.widget.BookletPageLayout, OO.ui.PageLayout );

	mw.ext.forms.widget.BookletPageLayout.prototype.setupOutlineItem = function() {
		if ( this.page.hasOwnProperty( 'label' ) ) {
			this.outlineItem.setLabel( this.page.label );
		}
		if ( this.page.hasOwnProperty( 'icon' ) ) {
			this.outlineItem.setIcon( this.page.icon );
		}
	};

} )( mediaWiki, jQuery, document );