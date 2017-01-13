function tongji(){
    var str_length = $("#huifu_content").val().length;
    var num = Number('500') - Number(str_length);
    $('#tongji').text(num);
    if (num < 0){
        var content = $("#huifu_content").val();
        var sub_string2 = content.substr(0,500);
        $("#huifu_content").val(sub_string2);
        $('#tongji').text('0');
        layer.msg('已达到输入最大值！', function(){
        });
    }
}