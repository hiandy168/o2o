$('form').attr('onsubmit','return encode()');
$(document).on('input','input[name="password_temp"]',function(){
    $('input[name="password"]').val($(this).val());
});
var encode_num = 0;
function encode() {
    if(encode_num == 0){
        encode_num = 1;
        var val = $('input[name="password"]').val();
        $('input[name="password"]').attr('name','password_temp');
        val = hex_md5(val);
        var str = '<input type="hidden" name="password" value="'+ val +'" />';
        $(str).appendTo('form');
    }
    return true;
}
