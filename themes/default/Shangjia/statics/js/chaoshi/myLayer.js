var fahuo_layer;
function tuikuan(option){
    layer.open({
        type: 2, //page层
        area: ['600px', '550px'],
        title: '<span style="font-weight:bold;">退款</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $(option).attr('url'),
    });
}
//function agree_tk(id){
//    alert("退款成功！");
//    layer.closeAll();
//}
function fahuo(option){
    layer.closeAll();
    fahuo_layer = layer.open({
        type: 2, //page层
        area: ['600px', '480px'],
        title: '<span style="font-weight:bold;">发货</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $(option).attr('url'),
    });
}
function tuihuo(option){
    layer.closeAll();
    fahuo_layer = layer.open({
        type: 2, //page层
        area: ['600px', '480px'],
        title: '<span style="font-weight:bold;">退货</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $(option).attr('url'),
    });
}




function xiugaijiage(option){
    layer.closeAll();
    layer.open({
        type: 2, //page层
        area: ['600px', '350px'],
        title: '<span style="font-weight:bold;">修改价格</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $(option).attr('url'),
    });
}

$(function(){
	$('#tijiaoxiugai').click(function(){
		$.post("",$("#form").serialize(),function(data){
			if(data.status == "success"){
				layer.msg(data.msg);
				//setTimeout("parent.layer.closeAll()",2000);
				setTimeout("parent.location.reload()",2000);				
			}else{
				layer.msg(data.msg);
			}			
		});
	})
	$('#tijiaofahuo').click(function(){
		$.post("",$("#form").serialize(),function(data){
			if(data.status == "success"){
				layer.msg(data.msg);
				//setTimeout("parent.layer.closeAll()",2000);
				setTimeout("parent.location.reload()",2000);				
			}else{
				layer.msg(data.msg);
			}			
		});
	})
	$('#cancel_order').click(function(){
		$.post("",$("#cancel_form").serialize(),function(data){
			if(data.status == "success"){
				layer.msg(data.msg);
				setTimeout("parent.location.reload()",2000);				
			}else{
				layer.msg(data.msg);
			}			
		});
	})
})

function quxiao(option){
    layer.closeAll();
    fahuo_layer = layer.open({
        type: 2, //page层
        area: ['600px', '480px'],
        title: '<span style="font-weight:bold;">取消订单</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $(option).attr('url'),
    });
}
function add_category(){
    layer.closeAll();
    layer.open({
        type: 1, //page层
        area: ['600px', '350px'],
        title: '<span style="font-weight:bold;">添加分类</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $('#add_category')
    });
}
function addCategory(){
    alert("分类添加！！");
    layer.closeAll();
}