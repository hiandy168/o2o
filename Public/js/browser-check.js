/*判断浏览器版本是否过低*/
$(document).ready(function() {
	var b_name = navigator.appName;
	var b_version = navigator.appVersion;
	var version = b_version.split(";");
	var trim_version = version[1].replace(/[ ]/g, "");
	if (b_name == "Microsoft Internet Explorer") {
		/*如果是IE6或者IE7或者IE8.0*/
		if (trim_version == "MSIE8.0" || trim_version == "MSIE7.0" || trim_version == "MSIE6.0") {
			layer.open({
				type:1,
				area:["500px","400px"],
				title:false,
				closeBtn:0,
				scrollbar:false,
				shade:[1,"#888"],
				content:$(".check-browser")

			})
		}
	}
}); 
