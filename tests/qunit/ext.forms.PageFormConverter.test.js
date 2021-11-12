( function ( mw, $ ) {
	QUnit.module( 'ext.blueSpiceExtendedSearch.utils', QUnit.newMwEnvironment() );

	QUnit.test( 'ext.blueSpiceExtendedSearch.utils.fragmentFunctions', function ( assert ) {
		//QUnit.expect( 1 );
		var obj = {
			a: 1000,
			b: [
				'A',
				'B',
				{
					c: 23
				}
			]
		};

		bs.extendedSearch.utils.setFragment( obj );
		var retrievedObj = bs.extendedSearch.utils.getFragment();

		assert.deepEqual( retrievedObj, obj, '#Fragment set/get works' );
	} );
}( mediaWiki, jQuery ) );
