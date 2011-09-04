/*
 * Tests for jQuery.fracs 0.9
 * http://larsjung.de/fracs
 * 
 * provided under the terms of the MIT License
 */

( function ( $ ) {

	var	Rect = $.fracs.internal.objects.Rect,
		FracsResult = $.fracs.internal.objects.FracsResult,
		FracsElement = $.fracs.internal.objects.FracsElement,
		FracsData = $.fracs.internal.objects.FracsData,
		FracsGroup = $.fracs.internal.objects.FracsGroup;
	
	
module( "Rect" );


test( "constructor", function () {

	var rect1 = new Rect( 30, 50, 400, 300 );
	strictEqual( rect1.left, 30, "left" );
	strictEqual( rect1.top, 50, "top" );
	strictEqual( rect1.width, 400, "width" );
	strictEqual( rect1.height, 300, "height" );
	strictEqual( rect1.right, 30 + 400, "right" );
	strictEqual( rect1.bottom, 50 + 300, "bottom" );
	strictEqual( rect1.area(), 400 * 300, "area" );

	var rect2 = new Rect( 30.1, 50.4, 400.3, 299.5 );
	strictEqual( rect2.left, 30, "left" );
	strictEqual( rect2.top, 50, "top" );
	strictEqual( rect2.width, 400, "width" );
	strictEqual( rect2.height, 300, "height" );
	strictEqual( rect2.right, 30 + 400, "right" );
	strictEqual( rect2.bottom, 50 + 300, "bottom" );
	strictEqual( rect2.area(), 400 * 300, "area" );
} );


test( "equals", function () {

	var rect1 = new Rect( 30, 50, 400, 300 );
	var rect2 = new Rect( 30.1, 50.4, 400.3, 299.5 );
	var rect3 = new Rect( 100, 200, 400, 300 );
	ok( rect1.equals( rect2 ), "equal rects" );
	ok( !rect1.equals( rect3 ), "unequal rects" );
} );


test( "intersection", function () {

	var rect1 = new Rect( 30, 50, 400, 300 );
	var rect2 = new Rect( 100, 200, 400, 300 );
	var intersection = new Rect( 100, 200, 330, 150 );
	deepEqual( rect1.intersection( rect2 ), intersection, "intersection" );
} );


test( "envelope", function () {

	var rect1 = new Rect( 30, 50, 400, 300 );
	var rect2 = new Rect( 100, 200, 400, 300 );
	var envelope = new Rect( 30, 50, 470, 450 );
	deepEqual( rect1.envelope( rect2 ), envelope, "envelope" );
} );



module( "static methods" );


test( "rect", function () {

	var $ele = $( "<div/>" )
		.appendTo( $( "body" ) )
		.css( {
			position: "absolute",
			left: "30px",
			top: "50px",
			width: "400px",
			height: "300px"
		} );
	var rect = $.fracs.rect( $ele );
	var expected  = new Rect( 30, 50, 400, 300 );
	deepEqual( rect, expected, "rect" );
} );


test( "round", function () {
	
	strictEqual( $.fracs.round( 1.234567 ), 1, "round 1.234567 with no precision" );
	strictEqual( $.fracs.round( 1.234567, -1 ), 1, "round 1.234567 to precision -1" );
	strictEqual( $.fracs.round( 1.234567, 0 ), 1, "round 1.234567 to precision 0" );
	strictEqual( $.fracs.round( 1.234567, 1 ), 1.2, "round 1.234567 to precision 1" );
	strictEqual( $.fracs.round( 1.234567, 2 ), 1.23, "round 1.234567 to precision 2" );
	strictEqual( $.fracs.round( 1.234567, 3 ), 1.235, "round 1.234567 to precision 3" );
	strictEqual( $.fracs.round( 1.234567, 4 ), 1.2346, "round 1.234567 to precision 4" );
} );


module( "methods" );




} )( jQuery );
