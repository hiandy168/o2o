
function choose(id) {
    var str = id.split('_');
    var content_id = 'content_'+str[2];
    $(document).ready(function () {
        $(".choose").addClass('nochoose');
        $(".choose").removeClass('choose');
        $(".on").removeClass('on');
        $("#" + id).removeClass('nochoose');
        $("#" + id).addClass('choose');
        $("#" + id).addClass('on');
        if (content_id == 'content_1'){
            $(".visibly").addClass('hide');
            $(".visibly").removeClass('visibly');
            $("#content_1").addClass('visibly');
            $("#content_1").removeClass('hide');
        }
        else{
            $("#content_1").addClass('hide');
            $(".visibly").addClass('hide');
            $(".visibly").removeClass('visibly');
            $("#"+content_id).addClass('visibly');
            $("#"+content_id).removeClass('hide');
        }
    });
    var nav_value = $(".on").text();
    $("#name_id").text(nav_value);
}
function chooseProductManage(id) {
    var str = id.split('_');
    var content_id = 'content_'+str[2];
    $(document).ready(function () {
        $(".choose").addClass('nochoose');
        $(".choose").removeClass('choose');
        $(".on").removeClass('on');
        $("#" + id).removeClass('nochoose');
        $("#" + id).addClass('choose');
        $("#" + id).addClass('on');
        if (content_id == 'content_1'){
            $(".visibly").addClass('hide');
            $(".visibly").removeClass('visibly');
            $("#content_1").addClass('visibly');
            $("#content_1").removeClass('hide');
        }
        else{
            $("#content_1").addClass('hide');
            $(".visibly").addClass('hide');
            $(".visibly").removeClass('visibly');
            $("#"+content_id).addClass('visibly');
            $("#"+content_id).removeClass('hide');
        }
        if (content_id == 'content_3'){
            $(".right").css("display","none");
        }
        else{
            $(".right").css("display","block");
        }
    });
    var nav_value = $(".on").text();
    $("#name_id").text(nav_value);
}

$(document).ready(function(){
    $('#fanli').click(function(){
        $('#zuigaofanli').css('display','block');
    });
    $('#bufanli').click(function(){
        $('#zuigaofanli').css('display','none');
    });
    $('#support').click(function(){
        $('#jianmian').css('display','block');
    });
    $('#nonsupport').click(function(){
        $('#jianmian').css('display','none');
    });
});



