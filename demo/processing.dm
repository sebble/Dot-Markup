<script type="text/javascript" src="processing.js"></script>
<script>
$(function(){
  $('canvas.pde').each(function(i,el){
    var source = $(el).data('processing-sources');
    if (source.substr(-3)=='src') {
        var string = $('#'+source.substring(0, source.length-4)+'_pde').html();
    } else {
        var string = $.ajax({type: "GET", url: source, async: false}).responseText;
    }
    new Processing.addInstance(new Processing(el, string));
  });
});
</script>

   = Processing.js

This document shows how easy it is to dynamically initialise a processing canvas after first page load.


<canvas data-processing-sources="pulse.pde" class="pde"></canvas>
<script type="application/processing" id="pulse_pde">
// Global variables
float radius = 50.0;
int X, Y;
int nX, nY;
int delay = 16;

// Setup the Processing Canvas
void setup(){
  size( 200, 200 );
  strokeWeight( 10 );
  frameRate( 15 );
  X = width / 2;
  Y = height / 2;
  nX = X;
  nY = Y;
}

// Main draw loop
void draw(){
  
  radius = radius + sin( frameCount / 4 );
  
  // Track circle to new destination
  X+=(nX-X)/delay;
  Y+=(nY-Y)/delay;
  
  // Fill canvas grey
  background( 100 );
  
  // Set fill-color to blue
  fill( 0, 121, 184 );
  
  // Set stroke-color white
  stroke(255);
  
  // Draw circle
  ellipse( X, Y, radius, radius );       
}


// Set circle's next destination
void mouseMoved(){
  nX = mouseX;
  nY = mouseY;
}
</script>
