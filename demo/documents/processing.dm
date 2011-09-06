<script type="text/javascript" src="processing.js"></script>
<script>
$(function(){
  $('canvas.processingjs').each(function(i,el){
    var source = $(el).data('processing-sources');
    if (source.substr(-3)=='src') {
        var string = $('#'+source.substring(0, source.length-4)).html();
    } else {
        var string = $.ajax({type: "GET", url: source, async: false}).responseText;
    }
    console.log([el]);
    //new Processing.addInstance(new Processing(el, string));
    new Processing(el, string);
  });
});
</script>

   = Processing.js

This document shows how easy it is to dynamically initialise a processing canvas after first page load.

  == Examples
  
 === Pulse

<canvas data-processing-sources="pulse.pde" class="processingjs"></canvas>

This demo uses an external file.

 === Circles

<canvas data-processing-sources="circles_pde.src" class="processingjs"></canvas>

:::
float max_distance;  
  
void setup() {  
  size(200, 200);   
  smooth();  
  noStroke();  
  max_distance = dist(0, 0, width, height);  
}  
  
void draw()   
{  
  background(51);  
  
  for(int i = 0; i <= width; i += 20) {  
    for(int j = 0; j <= width; j += 20) {  
      float size = dist(mouseX, mouseY, i, j);  
      size = size/max_distance * 66;  
      ellipse(i, j, size, size);  
    }  
  }  
} 
:::

<script type="application/processing" id="circles_pde">
float max_distance;  
  
void setup() {  
  size(200, 200);   
  smooth();  
  noStroke();  
  max_distance = dist(0, 0, width, height);  
}  
  
void draw()   
{  
  background(51);  
  
  for(int i = 0; i <= width; i += 20) {  
    for(int j = 0; j <= width; j += 20) {  
      float size = dist(mouseX, mouseY, i, j);  
      size = size/max_distance * 66;  
      ellipse(i, j, size, size);  
    }  
  }  
} 
</script>

 === Eyes

<canvas data-processing-sources="eyes_pde.src" class="processingjs"></canvas>

<script type="application/processing" id="eyes_pde">
// All Examples Written by Casey Reas and Ben Fry
// unless otherwise stated.
Eye e1, e2, e3, e4, e5;

void setup() 
{
  size(200, 200);
  smooth();
  noStroke();
  e1 = new Eye( 50,  16,  80);
  e2 = new Eye( 64,  85,  40);  
  e3 = new Eye( 90, 200, 120);
  e4 = new Eye(150,  44,  40); 
  e5 = new Eye(175, 120,  80);
}

void draw() 
{
  background(102);
  
  e1.update(mouseX, mouseY);
  e2.update(mouseX, mouseY);
  e3.update(mouseX, mouseY);
  e4.update(mouseX, mouseY);
  e5.update(mouseX, mouseY);

  e1.display();
  e2.display();
  e3.display();
  e4.display();
  e5.display();
}

class Eye 
{
  int ex, ey;
  int size;
  float angle = 0.0;
  
  Eye(int x, int y, int s) {
    ex = x;
    ey = y;
    size = s;
 }

  void update(int mx, int my) {
    angle = atan2(my-ey, mx-ex);
  }
  
  void display() {
    pushMatrix();
    translate(ex, ey);
    fill(255);
    ellipse(0, 0, size, size);
    rotate(angle);
    fill(153);
    ellipse(size/4, 0, size/2, size/2);
    popMatrix();
  }
}
</script>

:::
// All Examples Written by Casey Reas and Ben Fry
// unless otherwise stated.
Eye e1, e2, e3, e4, e5;

void setup() 
{
  size(200, 200);
  smooth();
  noStroke();
  e1 = new Eye( 50,  16,  80);
  e2 = new Eye( 64,  85,  40);  
  e3 = new Eye( 90, 200, 120);
  e4 = new Eye(150,  44,  40); 
  e5 = new Eye(175, 120,  80);
}

void draw() 
{
  background(102);
  
  e1.update(mouseX, mouseY);
  e2.update(mouseX, mouseY);
  e3.update(mouseX, mouseY);
  e4.update(mouseX, mouseY);
  e5.update(mouseX, mouseY);

  e1.display();
  e2.display();
  e3.display();
  e4.display();
  e5.display();
}

class Eye 
{
  int ex, ey;
  int size;
  float angle = 0.0;
  
  Eye(int x, int y, int s) {
    ex = x;
    ey = y;
    size = s;
 }

  void update(int mx, int my) {
    angle = atan2(my-ey, mx-ex);
  }
  
  void display() {
    pushMatrix();
    translate(ex, ey);
    fill(255);
    ellipse(0, 0, size, size);
    rotate(angle);
    fill(153);
    ellipse(size/4, 0, size/2, size/2);
    popMatrix();
  }
}
:::
