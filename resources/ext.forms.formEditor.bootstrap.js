$( () => {
	const $ctn = $( '#form-editor' );
	if ( $ctn.length ) {
		const editor = new mw.ext.forms.widget.FormEditor( {
			formData: $ctn.data( 'form-data' ),
			successRedirect: $ctn.data( 'success-redir' ),
			cancelRedirect: $ctn.data( 'cancel-redir' ),
			targetPage: $ctn.data( 'target-page' )
		} );
		$ctn.append( editor.$element );
	}
} );
