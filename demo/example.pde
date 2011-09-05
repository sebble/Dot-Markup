    class Shape { 
         void drawCircle(intx, int y, int radius) { 
              ellipse(x, y, radius, radius); 
        } 
         void drawTriangle(int x1, int y1, int x2, int y2, int x3, int 
y3) { 
              rect(x1, y1, x2, y2, x3, y3); 
        } 
    } 
    Shape shape = new Shape(); 
   shape.drawCircle(10, 40, 70);
