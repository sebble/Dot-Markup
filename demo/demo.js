
/*
    Demo Javascript
 */

////// Style Selector //////

$(function(){
    // create style selector
    var selector = $("<select></select>")
        .append("<option value=''> -- none -- </option>")
        .css({position:'absolute',top:'0',right:'0'})
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
});

////// Markup Parser //////

$(function(){
    // find textarea
});
