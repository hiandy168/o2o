$(function(){
    jQuery.validator.addMethod("isPhone", function(value, element) {
        var tel = /^(\d{3,4}-?)?\d{7,9}$/g;
        return this.optional(element) || (tel.test(value));
    }, "<font color=red>请正确填写您的电话号码。</font>");
    jQuery.validator.addMethod("isIdCardNo", function(value, element) {
        //var idCard = /^(\d{6})()?(\d{4})(\d{2})(\d{2})(\d{3})(\w)$/;
        return this.optional(element) || isIdCardNo(value);
    }, "<font color=red>请输入正确的身份证号码。</font>");
    jQuery.validator.addMethod("isMobile", function(value, element) {
        var length = value.length;
        return this.optional(element) || (length == 11 && /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/.test(value));
    }, "<font color=red>请正确填写您的手机号码。</font>");
    jQuery.validator.addMethod("isUploadImg", function(value, element) {
        return (value != "");
    }, "<font color=red style='position:absolute;margin-top:48px;'>请上传商品图片。</font>");


});
function isIdCardNo(num){
    //if (isNaN(num)) {alert("输入的不是数字！"); return false;}
    var len = num.length, re;
    if (len == 15)
        re = new RegExp(/^(\d{6})()?(\d{2})(\d{2})(\d{2})(\d{2})(\w)$/);
    else if (len == 18)
        re = new RegExp(/^(\d{6})()?(\d{4})(\d{2})(\d{2})(\d{3})(\w)$/);
    else {
        //alert("输入的数字位数不对。");
        return false;
    }
    var a = num.match(re);
    if (a != null)
    {
        if (len==15)
        {
            var D = new Date("19"+a[3]+"/"+a[4]+"/"+a[5]);
            var B = D.getYear()==a[3]&&(D.getMonth()+1)==a[4]&&D.getDate()==a[5];
        }
        else
        {
            var D = new Date(a[3]+"/"+a[4]+"/"+a[5]);
            var B = D.getFullYear()==a[3]&&(D.getMonth()+1)==a[4]&&D.getDate()==a[5];
        }
        if (!B) {
            //alert("输入的身份证号 "+ a[0] +" 里出生日期不对。");
            return false;
        }
    }
    if(!re.test(num)){
        //alert("身份证最后一位只能是数字和字母。");
        return false;
    }
    return true;
}

$().ready(function() {
    $("#dp_set").validate({
        rules: {
            "data[store_name]": "required",
            "data[address]": "required",
            "data[start_time]": "required",
            "data[end_time]": "required",
            "data[city_id]":{
                min:1
            },
            "data[phone]":{
                required:true,
                isPhone:true
            },
            "data[fan_money]":{
                required: "#fanli:checked",
                number:true
            },
            "data[full_money]":{
                required: "#support:checked",
                number:true
            },
            "data[discount_money]":{
                required: "#support:checked",
                number:true
            },
            "data[logistics]": {
                required: true,
                number: true
            },
            "data[since_money]": {
                required: true,
                number: true
            },
            "data[distribution]": {
                required: true,
                number: true
            },
            "data[distance]" :{
                required: true,
                number:true
            }
        },
        messages: {
            "data[store_name]": "<font color=red>请输入店铺名称</font>",
            "data[address]": "<font color=red>请输入地址</font>",
            "data[start_time]": "<font color=red>请输入配送开始时间</font>",
            "data[end_time]": "<font color=red>请输入配送结束时间</font>",
            "data[city_id]":{
                min:"<font color=red>请选择地区</font>"
            },
            "data[phone]": {
                required: "<font color=red>请输入电话号码</font>"
            },
            "data[fan_money]": {
                required: "<font color=red>请输入最高返利金额</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            "data[full_money]": {
                required: "<font color=red>请输入满多少金额可以减免费用</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            "data[discount_money]": {
                required: "<font color=red>请输入减免费用</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            "data[logistics]": {
                required: "<font color=red>请输入配送费</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            "data[since_money]": {
                required: "<font color=red>请输入起送价</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            "data[distribution]": {
                required: "<font color=red>请输入配送时间</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            "data[distance]" : {
                required: "<font color=red>请输入配送距离</font>",
                number: "<font color=red>只能输入数字</font>"
            }
        }
    });
    $("#product_info").validate({
        ignore: "",
        rules: {
            "data[product_name]": "required",
            "data[product_num]": "required",
            "data[inventory]":{
                required:true,
                number:true
            },
            "data[cate_id]":{
                min:1
            },
            "data[photo]":{
                isUploadImg:true
            },
            "data[price]":{
                required:true,
                number:true
            }
        },
        messages: {
            "data[product_name]": "<font color=red>请输入商品名称</font>",
            "data[product_num]": "<font color=red>请输入商品编号</font>",
            "data[inventory]" : {
                required: "<font color=red>请输入库存</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            "data[cate_id]": {
                min: "<font color=red>请选择分类</font>"
            },
            "data[price]" : {
                required: "<font color=red>请输入价格</font>",
                number: "<font color=red>只能输入数字</font>"
            }
        }
    });
});