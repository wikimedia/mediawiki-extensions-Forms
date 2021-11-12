( function ( mw, $, undefined ) {
	mw.ext.forms.editor.elementGroup.Layouts = function() {};

	OO.inheritClass( mw.ext.forms.editor.elementGroup.Layouts, mw.ext.forms.editor.elementGroup.Group );

	mw.ext.forms.editor.elementGroup.Layouts.prototype.getDisplayText = function() {
		return mw.message( 'forms-formelement-group-layout' ).text();
	};

	mw.ext.forms.editor.elementGroup.Layouts.prototype.getPriority = function() {
		return 20;
	};

	mw.ext.forms.editor.elementGroup.Layouts.prototype.getIcon = function() {
		return 'layout';
	};

	mw.ext.forms.registry.ElementGroup.register( "layout", new mw.ext.forms.editor.elementGroup.Layouts() );

} )( mediaWiki, jQuery );
