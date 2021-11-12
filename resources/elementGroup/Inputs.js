( function ( mw, $, undefined ) {
	mw.ext.forms.editor.elementGroup.Inputs = function() {};

	OO.inheritClass( mw.ext.forms.editor.elementGroup.Inputs, mw.ext.forms.editor.elementGroup.Group );

	mw.ext.forms.editor.elementGroup.Inputs.prototype.getDisplayText = function() {
		return mw.message( 'forms-formelement-group-input' ).text();
	};

	mw.ext.forms.editor.elementGroup.Inputs.prototype.getPriority = function() {
		return 10;
	};

	mw.ext.forms.editor.elementGroup.Inputs.prototype.getIcon = function() {
		return 'edit';
	};

	mw.ext.forms.registry.ElementGroup.register( "input", new mw.ext.forms.editor.elementGroup.Inputs() );

} )( mediaWiki, jQuery );
