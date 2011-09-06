
/*
    Demo Javascript
 */

////// Style Selector //////

$(function(){
    // create style selector
    var selector = $("<select id='style-select'></select>")
        .append("<option value=''> -- none -- </option>")
        .css({position:'fixed',top:'1em',right:'1em'})
        .change(function(e){
            var style = $(this).val();
            $('link[rel*="alt"]').attr('disabled','disabled');
            $('link[title="'+style+'"]').removeAttr('disabled','');
        });
    $('link[rel*="alt"]').each(function(i,el){
        var style = $(this).attr('title');
        selector.append($("<option value='"+style+"'>"+style+"</option>"));
    });
    // append to body
    $('body').append(selector);
    
    // create document selector
    var document = $("<form />")
        .css({position:'fixed',top:'1em',right:'53%',opacity:'0.2'})
        .append(
            $("<input />").attr('id','filename')
        )
        .append(
            $("<a />").text("Load").css({cursor:'pointer'})
            .click(function(){
                //console.log($('#filename').val()+'.dm');
                var fileName = $('#filename').val()+'.dm';
                var string = $.ajax({type: "GET", url: fileName, async: false}).responseText;
                $('#source').val(string).keyup();
            })
        ).submit(function(e){
            e.preventDefault();
            $('a',this).click();
        }).hover(function(){
            $(this).css({opacity:1})
        },function(){
            $(this).css({opacity:0.2})
        });
    $('body').append(document);
    
    
    // select basicplus
    $('#style-select').val('basicplus').change();
    $('#filename').val('documentation').parent().submit();
});

////// Markup Parser //////

$(function(){
    // find textarea
    $('#source').keyup(function(e){
        clearTimeout(window.updateTimer);
        window.updateTimer = setTimeout(function(){
            //$('#html').html($('#source').val());
            //console.log($('#source').val());
            $.post('demo.php', {text:$('#source').val()}, function(data){
                //console.log(data);
                $('#html').html(data);
                MathJax.Hub.Typeset();
                ToC2();
                //$( "#outline" ).fracs( 'outline', 'redraw' );
            }, 'html');
        }, 600);
    });
    
    $.post('demo.php', {text:$('#source').val()}, function(data){
        $('#html').html(data);
        MathJax.Hub.Typeset();
        ToC2();
    //    prepareOutline();
    }, 'html');
});

////// Canvas 'fracs' Outliner //////

//$(function(){
//function prepareOutline() {
/*    $( "#outline" ).fracs( "outline", {
			crop: true,
			styles: [
				{
					selector: "h1",
					strokeStyle: undefined,
					fillStyle: "rgba(255,0,153,1)"
				},
				{
					selector: "h2",
					strokeStyle: undefined,
					fillStyle: "rgba(153,0,255,1)"
				},
				{
					selector: "h3,h4",
					strokeStyle: undefined,
					fillStyle: "rgba(255,153,0,1)"
				},
				{
					selector: "table",
					strokeStyle: undefined,
					fillStyle: "rgba(0,153,255,0.5)",
					fillStyle: "rgba(153,153,153,0.1)"
				},
				{
					selector: "p",
					strokeStyle: undefined,
					fillStyle: "rgba(153,255,0,0.5)",
					fillStyle: "rgba(153,153,153,0.1)"
				},
				{
					selector: "pre,code",
					strokeStyle: undefined,
					fillStyle: "rgba(0,255,153,0.5)",
					fillStyle: "rgba(153,153,153,0.1)"
				}
			],
			viewportStyle:
			  {
					strokeWidth: "0",
					fillStyle: "rgba(200,200,200,0.5)",
					fillStyle: "rgba(153,255,0,0.5)"
				},
			viewportDragStyle:
			  {
					strokeWidth: "0",
					fillStyle: "rgba(200,200,200,0.7)",
					fillStyle: "rgba(0,153,255,0.5)",
					fillStyle: "rgba(0,255,153,0.5)"
				}
		} );*/
//});
//}

////// ToC Script //////

function ToC2() {

    $('#toc2').remove();
    var $list = $('<ul />').attr('id','toc2')
      .css({listStyle:'none', position:'fixed', top:'4em', right:'0em', bottom:'4em', overflow:'auto', margin:0, padding:'0.5em 0', borderLeft:'solid 2px #333', backgroundColor:'#fff'})
      .hover(function(){
          $(this).stop(true,true).animate({/*opacity:1, */marginRight:'0'});
      },function(){
          $(this).stop(true,true).animate({/*opacity:0.1, */marginRight:'-'+($(this).width()-15)+'px'});
      });
    
    $('h1,h2,h3,h4,h5,h6').each(function(){
        
        $this = $(this);
        
        if ($this.closest('form').length > 0) return; // avoid forms
        
        var id = makeid();
        var level = this.nodeName.toLowerCase().substr(1);
        var title = $this.text();
        $list.append(
            $('<li />').css({padding:0, margin:0, padding:'0.2em 1em', paddingLeft:(level-0)+'em'})
            .append(
                $('<a href="#'+id+'">'+title+'</a>')
            )
        );
        
        $this.html('<a name="'+id+'">'+$this.html()+'</a>');
    });
    
    $list.appendTo('body').mouseleave();
}

function makeid()
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 9; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}






