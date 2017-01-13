function tuikuan(id){
    layer.open({
        type: 1, //page层
        area: ['600px', '550px'],
        title: '<span style="font-weight:bold;">退款</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $('#tuikuanshenqing')
    });
}
function agree_tk(id){
    alert("退款成功！");
    layer.closeAll();
}
function fahuo(id){
    layer.closeAll();
    layer.open({
        type: 1, //page层
        area: ['600px', '480px'],
        title: '<span style="font-weight:bold;">发货</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $('#fahuo')
    });
}
function tijiaofahuo(id){
    alert("发货成功！");
    layer.closeAll();
}
function xiugaijiage(id){
    layer.closeAll();
    layer.open({
        type: 1, //page层
        area: ['600px', '350px'],
        title: '<span style="font-weight:bold;">修改价格</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $('#xiugaijiage')
    });
}
function tijiaoxiugai(id){
    alert("价格修改成功！！");
    layer.closeAll();
}
function quxiaodingdan(id){
    layer.closeAll();
    layer.open({
        type: 1, //page层
        area: ['600px', '350px'],
        title: '<span style="font-weight:bold;">发货</span>',
        shade: 0.6, //遮罩透明度
        moveType: 1, //拖拽风格，0是默认，1是传统拖动
        shift: 0, //0-6的动画形式，-1不开启
        content: $('#quxiaodingdan')
    });
}
function quxiao(id){
    alert("订单取消成功！");
    layer.closeAll();
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