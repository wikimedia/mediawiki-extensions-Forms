( function ( mw, $, undefined ) {
	mw.ext.forms.editor.elementGroup.Other = function() {};

	OO.inheritClass( mw.ext.forms.editor.elementGroup.Other, mw.ext.forms.editor.elementGroup.Group );

	mw.ext.forms.editor.elementGroup.Other.prototype.getDisplayText = function() {
		return mw.message( 'forms-formelement-group-other' ).text();
	};

	mw.ext.forms.editor.elementGroup.Other.prototype.getPriority = function() {
		return 30;
	};

	mw.ext.forms.editor.elementGroup.Other.prototype.getIcon = function() {
		return 'ellipsis';
	};

	mw.ext.forms.registry.ElementGroup.register( "other", new mw.ext.forms.editor.elementGroup.Other() );

} )( mediaWiki, jQuery );
