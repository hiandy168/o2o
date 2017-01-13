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
            dp_name: "required",
            lastname: "required",
            position: "required",
            tel:{
                required:true,
                isPhone:true
            },
            zuigaofanli:{
                required: "#fanli:checked",
                number:true
            },
            manduoshao:{
                required: "#support:checked",
                number:true
            },
            jianduoshao:{
                required: "#support:checked",
                number:true
            },
            username: {
                required: true,
                minlength: 2
            },
            password: {
                required: true,
                minlength: 5
            },
            confirm_password: {
                required: true,
                minlength: 5,
                equalTo: "#password"
            },
            email: {
                required: true,
                email: true
            },
            peisongfei: {
                required: true,
                number: true
            },
            qisongjia: {
                required: true,
                number: true
            },
            time: {
                required: true,
                number: true
            },
            distance:{
                required: true,
                number:true
            }
        },
        messages: {
            dp_name: "<font color=red>请输入店铺名称</font>",
            position: "<font color=red>请输入地址</font>",
            tel: {
                required: "<font color=red>请输入电话号码</font>"
            },
            zuigaofanli: {
                required: "<font color=red>请输入最高返利金额</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            manduoshao: {
                required: "<font color=red>请输入满多少金额可以减免费用</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            jianduoshao: {
                required: "<font color=red>请输入减免费用</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            peisongfei: {
                required: "<font color=red>请输入配送费</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            qisongjia: {
                required: "<font color=red>请输入起送价</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            time: {
                required: "<font color=red>请输入配送时间</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            distance: {
                required: "<font color=red>请输入配送距离</font>",
                number: "<font color=red>只能输入数字</font>"
            }
        }
    });
    $("#dp_info").validate({
        debug: true,
        rules: {
            companyName: "required",
            companyPosition: "required",
            companyPositionDetial: "required",
            companyPersonName:"required",
            companyPhone:{
                required:true,
                isPhone:true
            },
            idCard:{
                required:true,
                isIdCardNo:true
            },
            personPhone:{
                required: true,
                isPhone:true
            },
            personEmail:{
                required: true,
                email:true
            },
            daibiao:"required",
            bianhao: {
                required: true
            },
            fanwei:"required",
            confirm_password: {
                required: true,
                minlength: 5,
                equalTo: "#password"
            },
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            companyName: "<font color=red>请输入公司名称</font>",
            companyPosition: "<font color=red>请输入公司所在地</font>",
            companyPositionDetial: "<font color=red>请输入公司详细地址</font>",
            companyPersonName: "<font color=red>请输入联系人姓名</font>",
            fanwei:"<font color=red>请输入经营范围</font>",
            daibiao:"<font color=red>请输入法人代表</font>",
            companyPhone: {
                required: "<font color=red>请输入公司电话号码</font>"
            },
            idCard: {
                required: "<font color=red>请输入身份证号</font>"
            },
            personPhone: {
                required: "<font color=red>请输入电话号码</font>"
            },
            jianduoshao: {
                required: "<font color=red>请输入减免费用</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            peisongfei: {
                required: "<font color=red>请输入配送费</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            qisongjia: {
                required: "<font color=red>请输入起送价</font>",
                number: "<font color=red>只能输入数字</font>"
            },
            personEmail: {
                required: "<font color=red>请输入电子邮件地址</font>",
                email:"<font color=red>请输入有效的邮件地址</font>"
            },
            bianhao: {
                required: "<font color=red>请输入营业执照编号</font>"
            }
        }
    });
});