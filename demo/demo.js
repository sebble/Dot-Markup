
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
    
    // select basicplus
    $('#style-select').val('basicplus').change();
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
                MathJax.Hub.Typeset()
                //$( "#outline" ).fracs( 'outline', 'redraw' );
            }, 'html');
        }, 600);
    });
    
    $.post('demo.php', {text:$('#source').val()}, function(data){
        $('#html').html(data);
        MathJax.Hub.Typeset()
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
