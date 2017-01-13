/*
 * * global version 1.0
 * 
 *
 */

$(function() {
	$(document).on("mouseover", ".module-layout", function() {
		shopmpdlayout($(this));
	});
	$(document).on("mouseleave", ".layout-curr", function() {
		tmpLayoutClose();
	});
	$(document).on("mouseover", ".module-blank", function() {
		shopmpdblank($(this));
	});
	$(document).on("mouseleave", ".blank-curr", function() {
		tmpLayoutClose();
	});
	var hhTop= $(".topOne").outerHeight()+$(".topTwo").outerHeight(),hhTop_f=hhTop/2;
	$(".tmp-decoration-head").height(hhTop);
	$(".decoration-head-top,.decoration-head-ctrl").css({"height":(hhTop_f-20)+"px","lineHeight":(hhTop_f-20)+"px"});
});

function shopmpdlayout($this) {
	$(".layout-curr").remove();
	$("body").append('<div class="layoutAlpha layout-curr"><a href="javascript:;" data-uid="' + $this.attr("data-moduleid") + '" class="layout-edit"><i></i>编辑</a></div>');
	$(".layout-curr").css({
		"position": "absolute",
		"zIndex": "80",
		"border": "1px solid #40baf4",
		"width": $this.outerWidth() - 2,
		"height": $this.outerHeight() - 2,
		"left": $this.offset().left,
		"top": $this.offset().top
	});
}

function shopmpdblank($this) {
	$(".blank-curr").remove();
	$("body").append('<div class="blankAlpha blank-curr"></div>');
	$(".blank-curr").css({
		"position": "absolute",
		"zIndex": "80",
		"width": $this.outerWidth(),
		"height": $this.outerHeight(),
		"left": $this.offset().left,
		"top": $this.offset().top
	});
}

function tmpLayoutClose() {
	$(".layout-curr").remove();
	$(".blank-curr").remove();
}

$(document).on("click", ".tmphead-btn-close", function() {
	layer.closeAll();
});

var $bannerTb = $(".tmphead-edit-banner");
$(document).on("click",".tmphead-edit-banner .tmp-up",function() {
	var $parentTr = $(this).closest("tr");
	$parentTr.prev().before($parentTr);
});
$(document).on("click",".tmphead-edit-banner .tmp-down", function() {
	var $parentTr = $(this).closest("tr");
	$parentTr.next().after($parentTr);
});
$(document).on("click",".tmphead-edit-banner .tmp-del", function() {
	var $parentTr = $(this).closest("tr");
	var confirmIndex = layer.confirm('您确定要删除吗？', {
		btn: ['确定', '取消'],
		title:"删除提示"
	}, function() {
		$parentTr.remove();
		layer.close(confirmIndex);
	});
});
var jsonData={};
$(document).on("click",".tmphead-btn-add",function() {
	$(".tmphead-edit-banner").find("tr:last").after(template("tmpAddBanner",jsonData));
});
