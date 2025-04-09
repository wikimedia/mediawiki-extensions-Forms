( function ( mw ) {
	mw.ext.forms.target.WikipageTarget = function ( form, standalone ) {
		mw.ext.forms.target.WikipageTarget.parent.call( this, form, standalone );
	};

	OO.inheritClass( mw.ext.forms.target.WikipageTarget, mw.ext.forms.target.FormTarget );
}( mediaWiki ) );
