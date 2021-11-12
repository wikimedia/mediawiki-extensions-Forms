( function ( mw, $, d ) {
	mw.ext.forms.mixin.IndexLayoutParser = function() {};

	OO.initClass( mw.ext.forms.mixin.IndexLayoutParser );

	mw.ext.forms.mixin.IndexLayoutParser.prototype.parseIndexLayout = function( tabs, index ) {
		for ( var i = 0; i < tabs.length; i++ ) {
			var tab = tabs[i];
			if ( this.shouldShow( tab.showOn ) === false ) {
				continue;
			}

			var name = tab.name;
			var contLayout = new OO.ui.FieldsetLayout();
			var tabPanel = new OO.ui.TabPanelLayout( name, {
				expanded: false,
				framed: false,
				padded: true,
				label: tab.label,
				content: [ contLayout ]
			} );

			this.parseItems( tab.items || [], contLayout );

			index.addTabPanels( [ tabPanel ] );
		};
	};

} )( mediaWiki, jQuery, document );