



   = Inline Drawings
  == The Idea?

Can a simple keypress produce a sketching window, which when closed will save the image and insert it directly into the document?

Of course!

  == The Code:

Obviously you can't see this..

But you might see this image...

here --> [[img:sketches/ejssfdvhmy.png]] 

<script>
/* Initiation */
$.ctrl = function(key, callback, args) {
    $(document).keydown(function(e) {
        if(!args) args=[]; // IE barks when args is null
        if(e.keyCode == key.charCodeAt(0) && e.ctrlKey) {
            callback.apply(this, args);
            return false;
        }
    });
};

$.ctrl('I', function() {
    //insertLink();
    //console.log($('#the_plan').offset().top);
    $('#preview').scrollTop($('#the_plan').offset().top);
},null);
</script>

  == The Plan!

{{{
<style>
</style>
<div id="the_plan">
<p><canvas id="drawingCanvas" width="550" height="450" style="border:1pt solid black;margin:auto;cursor:crosshair;clear:both;">
</canvas></p>

<p><a href="#" class="colorPicker" onclick="setColor('#FFF');return false;" style="background:#FFF;">W</a>
<a class="colorPicker" href="#" onclick="setColor('#000');return false;" style="background:#000;">B</a>
<a class="colorPicker" href="#" onclick="setColor('#FF0000');return false;" style="background:#FF0000;">R</a>
<a class="colorPicker" href="#" onclick="setColor('#00FF00');return false;" style="background:#00FF00;">G</a>
<a class="colorPicker" href="#" onclick="setColor('#0000FF');return false;" style="background:#0000FF;">B</a>
<a class="colorPicker" href="#" onclick="setColor('#FFFF00');return false;" style="background:#FFFF00;">Y</a>
<a class="colorPicker" href="#" onclick="setColor('#00FFFF');return false;" style="background:#00FFFF;">C</a>

<a href="#" class="colorPicker" onclick="setSize(2);return false;" style="width:2px;height:2px;margin-left:15px;">2</a>
<a href="#" class="colorPicker" onclick="setSize(5);return false;" style="width:5px;height:5px;margin-left:15px;">5</a>
<a href="#" class="colorPicker" onclick="setSize(10);return false;" style="width:10px;height:10px;margin-left:15px;">10</a>
<a href="#" class="colorPicker" onclick="setSize(25);return false;" style="width:25px;height:25px;margin-left:15px;">25</a></p>

<p style="clear:both;"><input type="button" value="Clear Canvas" onclick="clearCanvas();">
<input type="button" value="Save My Drawing" onclick="saveDrawing();"></p>
</div>


<script type="text/javascript">
/* some code used from http://www.williammalone.com/articles/create-html5-canvas-javascript-drawing-app/ */

/* Some global initializations follow. The first 2 arays will store all mouse positions 
on X and Y, the 3rd one stores the dragged positions. 
The variable paint is a boolean, and then follow the default values 
which we use to start */
var clickX = new Array();
var clickY = new Array();
var clickDrag = new Array();
var paint;
var defaultColor="#000";
var defaultShape="round";
var defaultWidth=5;

// creating the canvas element
var canvas = document.getElementById('drawingCanvas');

if(canvas.getContext) 
{
    // Initaliase a 2-dimensional drawing context
    var context = canvas.getContext('2d');
    
    // set the defaults
    context.strokeStyle = defaultColor;
    context.lineJoin = defaultShape;
    context.lineWidth = defaultWidth;
}

// binding events to the canvas
$('#drawingCanvas').mousedown(function(e){
  var mouseX = e.pageX - this.offsetLeft;
  var mouseY = e.pageY - this.offsetTop;
  
  paint = true; // start painting
  addClick(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);

  // always call redraw
  redraw();
});

$('#drawingCanvas').mousemove(function(e){
  if(paint){
    addClick(e.pageX - this.offsetLeft, e.pageY - this.offsetTop, true);
    redraw();
  }
});

// when mouse is released, stop painting, clear the arrays with dots
$('#drawingCanvas').mouseup(function(e){
  paint = false;
  
  clickX = new Array();
  clickY = new Array();
  clickDrag = new Array();
});

// stop painting when dragged out of the canvas
$('#drawARobot').mouseleave(function(e){
  paint = false;
});

// The function pushes to the three dot arrays
function addClick(x, y, dragging)
{
  clickX.push(x);
  clickY.push(y);
  clickDrag.push(dragging);
}

// this is where actual drawing happens
// we add dots to the canvas
function redraw(){
    
  for(var i=0; i < clickX.length; i++)
  {  
    context.beginPath();
    if(clickDrag[i] && i){
      context.moveTo(clickX[i-1], clickY[i-1]);
     }else{
       context.moveTo(clickX[i]-1, clickY[i]);
     }
     context.lineTo(clickX[i], clickY[i]);
     context.closePath();
     context.stroke();
  }
}

// this is called when "clear canvas" button is pressed
function clearCanvas()
{
    // both these lines are required to clear the canvas properly in all browsers
    context.clearRect(0,0,canvas.width,canvas.height);
    canvas.width = canvas.width;
    
    // we need to flush the arrays too
    clickX = new Array();
    clickY = new Array();
    clickDrag = new Array();
}

/* Two simple functions, they just assign the selected color and size 
to the canvas object properties */ 
function setColor(col)
{
    context.strokeStyle = col;
}

function setSize(px)
{
    context.lineWidth=px;
}

/* Finally this will send your image to the server-side script which will 
save it to the database or where ever you want it saved.
Note that this function should be called when the button in your save 
form is pressed. The variable frm is the form object. 
Basically the HTML will look like this:
<input type="button" value="Save Drawing" onclick="saveDrawing(this.form);">
 */
function saveDrawing()
{       
    // converting the canvas to data URI
    var strImageData = canvas.toDataURL();  
        //console.log(strImageData);
    $.ajax({
        url: "saveSketch.php", /* You need to enter the URL of your server side script*/
        type: "post",
          /* add the other variables here or serialize the entire form. 
          Image data must be URI encoded */
        data: "save=1&pic="+encodeURIComponent(strImageData), 
        success: function(msg)
        {
           // display some message and/or redirect
           console.log(msg);
insertLink(msg);
//$('#the_plan').hide();
        }
    });
}

function insertLink(name) {

    var myValue = '[[img#'+name+':sketches/'+name+'.png]]';
    var $ta = $('#source');
    replaceIt($ta[0], myValue);

    si = true;
    sid = name;
    $ta.keyup();

    //$('#preview').scrollTop($('#'+name).offset().top);
}
function replaceIt(txtarea, newtxt) {
  $(txtarea).val(
        $(txtarea).val().substring(0, txtarea.selectionStart)+
        newtxt+
        $(txtarea).val().substring(txtarea.selectionEnd)
   );  
}
</script>
}}}

