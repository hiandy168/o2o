/*
 * * global version 1.2.5
 * 
 * jQuery Easing v1.3 - http://gsgd.co.uk/sandbox/jquery/easing/
 * Uses the built in easing capabilities added In jQuery 1.1
 * to offer multiple easing options
 *
 * TERMS OF USE - jQuery Easing
 * 
 * Open source under the BSD License. 
 * 
 * Copyright 漏 2008 George McGinley Smith
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list 
 * of conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * 
 * Neither the name of the author nor the names of contributors may be used to endorse 
 * or promote products derived from this software without specific prior written permission.
 * download by http://www.codefans.net
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 *  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 *  GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED 
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE. 
 *
 */

// t: current time, b: begInnIng value, c: change In value, d: duration
jQuery.easing['jswing'] = jQuery.easing['swing'];

jQuery.extend(jQuery.easing, {
	def: 'easeOutQuad',
	swing: function(x, t, b, c, d) {
		//alert(jQuery.easing.default);
		return jQuery.easing[jQuery.easing.def](x, t, b, c, d);
	},
	easeInQuad: function(x, t, b, c, d) {
		return c * (t /= d) * t + b;
	},
	easeOutQuad: function(x, t, b, c, d) {
		return -c * (t /= d) * (t - 2) + b;
	},
	easeInOutQuad: function(x, t, b, c, d) {
		if ((t /= d / 2) < 1) return c / 2 * t * t + b;
		return -c / 2 * ((--t) * (t - 2) - 1) + b;
	},
	easeInCubic: function(x, t, b, c, d) {
		return c * (t /= d) * t * t + b;
	},
	easeOutCubic: function(x, t, b, c, d) {
		return c * ((t = t / d - 1) * t * t + 1) + b;
	},
	easeInOutCubic: function(x, t, b, c, d) {
		if ((t /= d / 2) < 1) return c / 2 * t * t * t + b;
		return c / 2 * ((t -= 2) * t * t + 2) + b;
	},
	easeInQuart: function(x, t, b, c, d) {
		return c * (t /= d) * t * t * t + b;
	},
	easeOutQuart: function(x, t, b, c, d) {
		return -c * ((t = t / d - 1) * t * t * t - 1) + b;
	},
	easeInOutQuart: function(x, t, b, c, d) {
		if ((t /= d / 2) < 1) return c / 2 * t * t * t * t + b;
		return -c / 2 * ((t -= 2) * t * t * t - 2) + b;
	},
	easeInQuint: function(x, t, b, c, d) {
		return c * (t /= d) * t * t * t * t + b;
	},
	easeOutQuint: function(x, t, b, c, d) {
		return c * ((t = t / d - 1) * t * t * t * t + 1) + b;
	},
	easeInOutQuint: function(x, t, b, c, d) {
		if ((t /= d / 2) < 1) return c / 2 * t * t * t * t * t + b;
		return c / 2 * ((t -= 2) * t * t * t * t + 2) + b;
	},
	easeInSine: function(x, t, b, c, d) {
		return -c * Math.cos(t / d * (Math.PI / 2)) + c + b;
	},
	easeOutSine: function(x, t, b, c, d) {
		return c * Math.sin(t / d * (Math.PI / 2)) + b;
	},
	easeInOutSine: function(x, t, b, c, d) {
		return -c / 2 * (Math.cos(Math.PI * t / d) - 1) + b;
	},
	easeInExpo: function(x, t, b, c, d) {
		return (t == 0) ? b : c * Math.pow(2, 10 * (t / d - 1)) + b;
	},
	easeOutExpo: function(x, t, b, c, d) {
		return (t == d) ? b + c : c * (-Math.pow(2, -10 * t / d) + 1) + b;
	},
	easeInOutExpo: function(x, t, b, c, d) {
		if (t == 0) return b;
		if (t == d) return b + c;
		if ((t /= d / 2) < 1) return c / 2 * Math.pow(2, 10 * (t - 1)) + b;
		return c / 2 * (-Math.pow(2, -10 * --t) + 2) + b;
	},
	easeInCirc: function(x, t, b, c, d) {
		return -c * (Math.sqrt(1 - (t /= d) * t) - 1) + b;
	},
	easeOutCirc: function(x, t, b, c, d) {
		return c * Math.sqrt(1 - (t = t / d - 1) * t) + b;
	},
	easeInOutCirc: function(x, t, b, c, d) {
		if ((t /= d / 2) < 1) return -c / 2 * (Math.sqrt(1 - t * t) - 1) + b;
		return c / 2 * (Math.sqrt(1 - (t -= 2) * t) + 1) + b;
	},
	easeInElastic: function(x, t, b, c, d) {
		var s = 1.70158;
		var p = 0;
		var a = c;
		if (t == 0) return b;
		if ((t /= d) == 1) return b + c;
		if (!p) p = d * .3;
		if (a < Math.abs(c)) {
			a = c;
			var s = p / 4;
		} else var s = p / (2 * Math.PI) * Math.asin(c / a);
		return -(a * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p)) + b;
	},
	easeOutElastic: function(x, t, b, c, d) {
		var s = 1.70158;
		var p = 0;
		var a = c;
		if (t == 0) return b;
		if ((t /= d) == 1) return b + c;
		if (!p) p = d * .3;
		if (a < Math.abs(c)) {
			a = c;
			var s = p / 4;
		} else var s = p / (2 * Math.PI) * Math.asin(c / a);
		return a * Math.pow(2, -10 * t) * Math.sin((t * d - s) * (2 * Math.PI) / p) + c + b;
	},
	easeInOutElastic: function(x, t, b, c, d) {
		var s = 1.70158;
		var p = 0;
		var a = c;
		if (t == 0) return b;
		if ((t /= d / 2) == 2) return b + c;
		if (!p) p = d * (.3 * 1.5);
		if (a < Math.abs(c)) {
			a = c;
			var s = p / 4;
		} else var s = p / (2 * Math.PI) * Math.asin(c / a);
		if (t < 1) return -.5 * (a * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p)) + b;
		return a * Math.pow(2, -10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p) * .5 + c + b;
	},
	easeInBack: function(x, t, b, c, d, s) {
		if (s == undefined) s = 1.70158;
		return c * (t /= d) * t * ((s + 1) * t - s) + b;
	},
	easeOutBack: function(x, t, b, c, d, s) {
		if (s == undefined) s = 1.70158;
		return c * ((t = t / d - 1) * t * ((s + 1) * t + s) + 1) + b;
	},
	easeInOutBack: function(x, t, b, c, d, s) {
		if (s == undefined) s = 1.70158;
		if ((t /= d / 2) < 1) return c / 2 * (t * t * (((s *= (1.525)) + 1) * t - s)) + b;
		return c / 2 * ((t -= 2) * t * (((s *= (1.525)) + 1) * t + s) + 2) + b;
	},
	easeInBounce: function(x, t, b, c, d) {
		return c - jQuery.easing.easeOutBounce(x, d - t, 0, c, d) + b;
	},
	easeOutBounce: function(x, t, b, c, d) {
		if ((t /= d) < (1 / 2.75)) {
			return c * (7.5625 * t * t) + b;
		} else if (t < (2 / 2.75)) {
			return c * (7.5625 * (t -= (1.5 / 2.75)) * t + .75) + b;
		} else if (t < (2.5 / 2.75)) {
			return c * (7.5625 * (t -= (2.25 / 2.75)) * t + .9375) + b;
		} else {
			return c * (7.5625 * (t -= (2.625 / 2.75)) * t + .984375) + b;
		}
	},
	easeInOutBounce: function(x, t, b, c, d) {
		if (t < d / 2) return jQuery.easing.easeInBounce(x, t * 2, 0, c, d) * .5 + b;
		return jQuery.easing.easeOutBounce(x, t * 2 - d, 0, c, d) * .5 + c * .5 + b;
	}
});

/*
 *
 * TERMS OF USE - EASING EQUATIONS
 * 
 * Open source under the BSD License. 
 * 
 * Copyright 漏 2001 Robert Penner
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list 
 * of conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * 
 * Neither the name of the author nor the names of contributors may be used to endorse 
 * or promote products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 *  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 *  GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED 
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE. 
 *
 *      linear，swing，jswing，easeInQuad，easeOutQuad，easeInOutQuad，easeInCubic， easeOutCubic，easeInOutCubic，
		easeInQuart，easeOutQuart，easeInOutQuart， easeInQuint，easeOutQuint，easeInOutQuint，easeInSine，easeOutSine，
		 easeInOutSine，easeInExpo，easeOutExpo，easeInOutExpo，easeInCirc， easeOutCirc，easeInOutCirc，easeInElastic，
		easeOutElastic，easeInOutElastic， easeInBack，easeOutBack，easeInOutBack，easeInBounce，easeOutBounce，easeInOutBounce.
		$(element).animate({ 
		    height:500, 
		    width:600 
		    },{ 
		    easing: 'easeInOutQuad', 
		    duration: 500, 
		    complete: callback 
		}); 
 * 
 */

(function() {
	var lastTime = 0;
	var vendors = ['webkit', 'moz'];
	for (var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
		window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
		window.cancelAnimationFrame = window[vendors[x] + 'CancelAnimationFrame'] || // name has changed in Webkit
			window[vendors[x] + 'CancelRequestAnimationFrame'];
	}

	if (!window.requestAnimationFrame) {
		window.requestAnimationFrame = function(callback, element) {
			var currTime = new Date().getTime();
			var timeToCall = Math.max(0, 16.7 - (currTime - lastTime));
			var id = window.setTimeout(function() {
				callback(currTime + timeToCall);
			}, timeToCall);
			lastTime = currTime + timeToCall;
			return id;
		};
	}
	if (!window.cancelAnimationFrame) {
		window.cancelAnimationFrame = function(id) {
			clearTimeout(id);
		};
	}
}());

/*var moveStyle = "margin", testDiv = document.createElement("div");
	if ("oninput" in testDiv) {
		["", "ms", "webkit"].forEach(function(prefix) {
			var transform = prefix + (prefix? "T": "t") + "ransform";
			if (transform in testDiv.style) {
				moveStyle = transform;
			}
		});		
	}*/


var cartFunction = {
	getType: function(b) {
		return Object.prototype.toString.call(b).match(/^\[object\s(.*)\]$/)[1]
	},
	isTypeOf: function(b, a) {
		return this.getType(b) == a
	},
	$objct: {
		cartFoot: ".cart-foot"
	},
	init: function(b) {
		var a = $.extend({
			addCart: {
				ajaxUrl: "",
				ajaxParameter: {},
				ajaxType: "GET",
				ajaxDataType: "json",
				ajaxTimeout: 3E4
			},
			specsName: {
				ajaxUrl: "",
				ajaxType: "GET",
				ajaxDataType: "json",
				ajaxTimeout: 3E4
			},
			specsItem: {
				ajaxUrl: "",
				ajaxParameter: {},
				ajaxType: "GET",
				ajaxDataType: "json",
				ajaxTimeout: 3E4
			},
			specsInfo: {
				ajaxUrl: "",
				ajaxType: "GET",
				ajaxDataType: "json",
				ajaxTimeout: 3E4
			},
			cartNumAdd: {
				ajaxUrl: "",
				ajaxType: "GET",
				ajaxDataType: "json",
				ajaxTimeout: 3E4
			},
			cartNumReduce: {
				ajaxUrl: "",
				ajaxType: "GET",
				ajaxDataType: "json",
				ajaxTimeout: 3E4
			},
			cartDelList: {
				ajaxUrl: "",
				ajaxType: "GET",
				ajaxDataType: "json",
				ajaxTimeout: 3E4
			},
			cartUpdate: {
				ajaxUrl: "",
				ajaxType: "GET",
				ajaxDataType: "json",
				ajaxTimeout: 3E4
			}
		}, {
			initOpen: !0,
			addCartOpen: !1,
			cartShopSize: !1
		}, b);
		a.initOpen ? $(".cart-content-list").children().size() ? cartFunction.cartOpen() : cartFunction.cartClose() :
			cartFunction.cartClose();
		$(".add-cart-submit").on("click", function(b) {
			var e = parseInt($(this).attr("data-id")),
				d = $(this).closest("li").find("input.cart-number-text").val(),
				h = b.pageX,
				g = b.pageY,
				k = $("#specificationsItem");
			if ($(this).hasClass("notlists"))
				if (k.find(".specifications").size() == k.find("li.current").size()) {
					b = parseInt(k.find(".specifications:last").find("li.current a").attr("data-id"));
					var l = $("#cart-buy-number").find("input.buy-num").val();
					$.ajax({
						url: a.addCart.ajaxUrl,
						type: a.addCart.ajaxType,
						data: {
							spec_id: b,
							quantity: l
						},
						dataType: a.addCart.ajaxDataType,
						timeout: a.addCart.ajaxTimeout,
						success: function(b) {
							b && cartFunction.goCart(h, g, b, a)
						},
						error: function(a, b, d) {
							layer.msg("\u64cd\u4f5c\u5931\u8d25\uff01")
						}
					})
				} else layer.msg("\u8bf7\u9009\u62e9\u89c4\u683c\u53c2\u6570\uff01");
			else {
				var m = "";
				$(".cart-spec-info .specifications dt,.cart-spec-info .specifications dd ul").empty();
				layer.open({
					type: 1,
					title: "\u9009\u62e9\u89c4\u683c\u4fe1\u606f",
					skin: "layui-layer-rim",
					area: ["420px", "280px"],
					content: $(".cart-spec-info")
				});
				var n = layer.load(2, {
					time: 3E4
				});
				$.ajax({
					url: a.specsItem.ajaxUrl,
					type: a.specsItem.ajaxType,
					data: {
						goods_id: e
					},
					dataType: a.specsItem.ajaxDataType,
					timeout: a.specsItem.ajaxTimeout,
					success: function(a) {
						layer.close(n);
						if (a && 1 == a.status) {
							a = a.data;
							for (var b = 0; b < a.length; b++) m += '<li><a href="javascript:void(0);" data-name="' + a[b] + '">' + a[b] + "</a></li>";
							$(".cart-spec-info .specifications:eq(0)").find("ul").empty().append(m);
							$(".cart-spec-info .specifications:eq(0)").find("li:first").find("i").remove();
							$(".cart-spec-info .specifications:eq(0)").find("li:first").addClass("current").append("<i></i>");
							"" != a[0] ? (a = $(".cart-spec-info .specifications").find("li:first").find("a").attr("data-name"), $(".cart-spec-info .specifications:eq(0)").show()) : (a = null, $(".cart-spec-info .specifications:eq(0)").hide());
							c({
								goods_id: e,
								select_specs_1_name: a
							})
						}
					},
					error: function(a, b, d) {
						layer.close(n);
						layer.msg("\u64cd\u4f5c\u5931\u8d25\uff01")
					}
				});
				$(document).on("click", ".cart-spec-info .specifications dd li a", function() {
					$(this).parent().addClass("current").siblings("li").removeClass("current");
					$(this).parent().siblings("li").find("i").remove();
					$(this).parent().find("i").remove();
					$(this).after("<i></i>");
					$(this).attr("data-name") && (c({
						goods_id: e,
						select_specs_1_name: parseInt($(this).attr("data-name"))
					}), $(".cart-spec-info .specifications:last").find("li.current").removeClass("current"));
					$(this).attr("data-price") && $("#layer-data-price").empty().html("\uffe5" + $(this).attr("data-price"))
				});
				$.ajax({
					url: a.specsName.ajaxUrl,
					type: a.specsName.ajaxType,
					data: {
						goods_id: e
					},
					dataType: a.specsName.ajaxDataType,
					timeout: a.specsName.ajaxTimeout,
					success: function(a) {
						layer.close(n);
						a && 1 == a.status && (a = a.data, $(".cart-spec-info .specifications:eq(0) dt").empty().html(a.spec_name_1 + "\uff1a"), $(".cart-spec-info .specifications:eq(1) dt").empty().html(a.spec_name_2 + "\uff1a"), $("#layer-data-price").empty().html("\uffe5" + a.price))
					},
					error: function(a, b, d) {
						layer.msg("\u64cd\u4f5c\u5931\u8d25\uff01")
					}
				});
				$(".cart-spec-confirm").unbind("click");
				$(".cart-spec-confirm").on("click", function() {
					if (k.find(".specifications").size() == k.find("li.current").size()) {
						var b = k.find(".specifications:last").find("li.current a").attr("data-id");
						$.ajax({
							url: a.addCart.ajaxUrl,
							type: a.addCart.ajaxType,
							data: {
								spec_id: b,
								quantity: d
							},
							dataType: a.addCart.ajaxDataType,
							timeout: a.addCart.ajaxTimeout,
							success: function(b) {
								b && (layer.closeAll(), cartFunction.goCart(h, g, b, a))
							},
							error: function(a, b, d) {
								layer.msg("\u64cd\u4f5c\u5931\u8d25\uff01")
							}
						})
					} else layer.msg("\u8bf7\u9009\u62e9\u89c4\u683c\u53c2\u6570\uff01")
				})
			}
			return !1
		});
		var c = function(b) {
			var c = "",
				d = layer.load(2, {
					time: 3E4
				});
			$.ajax({
				url: a.specsInfo.ajaxUrl,
				type: a.specsInfo.ajaxType,
				data: b,
				dataType: a.specsInfo.ajaxDataType,
				timeout: a.specsInfo.ajaxTimeout,
				success: function(a) {
					layer.close(d);
					if (a && 1 == a.status) {
						a = a.data;
						for (var b = 0; b < a.length; b++) c += '<li><a href="javascript:void(0);" data-id="' + a[b].spec_id + '" data-price="' + a[b].price + '">' + a[b].spec_2 + "</a></li>";
						$(".cart-spec-info .specifications dd:eq(1)").find("ul").empty().append(c)
					}
				},
				error: function(a, b, c) {
					layer.close(d);
					layer.msg("\u64cd\u4f5c\u5931\u8d25\uff01")
				}
			})
		};
		$(cartFunction.$objct.cartFoot).on("click", function() {
			$(".cart-content-list").children().size() ? $(this).hasClass("curr") ?
				cartFunction.cartClose() : cartFunction.cartOpen() : cartFunction.cartClose()
		});
		$(".global-cart .cart-btn").on("click", function(a) {
			a.stopPropagation()
		});
		cartFunction.deleteCart(a);
		cartFunction.calculation(".cart-content-list .btn-reduce", ".buy-num", ".cart-content-list .btn-add", "li", a);
		cartFunction.arithmetic(a)
	},
	arithmetic: function(b) {
		var a = $(".global-cart .cart-buy-price"),
			c = $(window).width() / 2,
			f = $(".global-cart").offset().top - $(".cart-body").outerHeight() / 2,
			e = 1;
		$(document).on("click", ".cart-content-list .btn-add",
			function() {
				var d = $(this).closest("li"),
					h = parseInt(d.find("input.item-check").attr("data-sn")),
					g = d.find("input.buy-num");
				parseInt(g.val()) <= parseInt(g.attr("data-max")) && e != parseInt(g.attr("data-max")) && $.ajax({
					url: b.cartNumAdd.ajaxUrl,
					type: b.cartNumAdd.ajaxType,
					data: {
						cart_id: h
					},
					dataType: b.cartNumAdd.ajaxDataType,
					timeout: b.cartNumAdd.ajaxTimeout,
					success: function(b) {
						d.find(".price strong").html("\uffe5" + b.data.single_total);
						g.val(parseInt(b.data.goods_number));
						a.html("\uffe5" + b.data.total.total);
						e <=
							parseInt(g.attr("data-max")) && cartFunction.goCart(c, f);
						e = parseInt(b.data.goods_number)
					},
					error: function(a, b, d) {
						layer.msg("\u64cd\u4f5c\u5931\u8d25\uff01")
					}
				})
			});
		$(document).on("click", ".cart-content-list .btn-reduce", function() {
			var d = $(this).closest("li"),
				c = parseInt(d.find("input.item-check").attr("data-sn")),
				g = d.find("input.buy-num");
			1 < parseInt(g.val()) && $.ajax({
				url: b.cartNumReduce.ajaxUrl,
				type: b.cartNumReduce.ajaxType,
				data: {
					cart_id: c
				},
				dataType: b.cartNumReduce.ajaxDataType,
				timeout: b.cartNumReduce.ajaxTimeout,
				success: function(b) {
					d.find(".price strong").html("\uffe5" + b.data.single_total);
					d.find("input.buy-num").val(parseInt(b.data.goods_number));
					a.html("\uffe5" + b.data.total.total);
					e = parseInt(b.data.goods_number)
				},
				error: function(a, b, d) {
					layer.msg("\u64cd\u4f5c\u5931\u8d25\uff01")
				}
			})
		})
	},
	goCart: function(b, a, c, f) {
		var e = $(".cart-icon").offset(),
			d = $('<div class="cart-ball"></div>');
		0 != c.status ? d.fly({
			start: {
				left: b,
				top: a,
				width: 20,
				height: 20
			},
			end: {
				left: e.left + 10,
				top: e.top + 10,
				width: 20,
				height: 20
			},
			onEnd: function() {
				if (c) {
					if (1 ==
						c.status) cartFunction.addCartlist(c.data.str), $(".global-cart .cart-buy-price").empty().html("\uffe5" + c.data.total.price);
					else if (2 == c.status) {
						$(".global-cart .cart-buy-price").empty().html("\uffe5" + c.data.total.total);
						var a = $(".global-cart input[data-sn='" + c.data.cart_id + "']").closest("li");
						a.find("input.buy-num").val(parseInt(c.data.goods_number));
						a.find(".price strong").empty().html("\uffe5" + c.data.single_total)
					}
					f.addCartOpen || $(cartFunction.$objct.cartFoot).hasClass("curr") ? cartFunction.cartOpen() :
						cartFunction.cartClose();
					cartFunction.cartShopSize(f, c)
				}
				this.destory()
			}
		}) : layer.msg(c.msg)
	},
	addCartlist: function(b) {
		$(".cart-content-list").append(b);
		$("#cart-check-all").prop("checked", !1)
	},
	cartOpen: function() {
		$(".cart-body").each(function() {
			$(cartFunction.$objct.cartFoot).addClass("curr");
			var b = $(this),
				a = $(".global-cart .cart-head");
			b.stop().animate({
				top: -b.outerHeight()
			}, 500, "", function() {
				$(".global-cart .cart-sum-pirce").fadeOut("swing");
				$(".global-cart .del-btn").delay(500).fadeIn("swing")
			});
			a.stop().animate({
				top: "-" + (b.outerHeight() + a.outerHeight()) + "px"
			}, 500, "")
		});
		cartFunction.checkAll(".cart-content-list input[type='checkbox']", "#cart-check-all")
	},
	cartClose: function() {
		$(".cart-body").each(function() {
			$(cartFunction.$objct.cartFoot).removeClass("curr");
			var b = $(this),
				a = $(".global-cart .cart-head");
			b.stop().animate({
				top: 0
			}, 500, "", function() {
				$(".cart-content-list").children().size() ? ($(".global-cart .del-btn").fadeOut("swing"), $(".global-cart .cart-sum-pirce").delay(500).fadeIn("swing")) :
					($(".global-cart .del-btn").hide(), $(".global-cart .cart-sum-pirce").hide())
			});
			a.stop().animate({
				top: -a.outerHeight()
			}, 500, "")
		})
	},
	autoAdd: function() {
		$(".cart-body").each(function() {
			$(cartFunction.$objct.cartFoot).addClass("curr");
			var b = $(this);
			b.stop().animate({
				height: b.find("ul.cart-content-list").outerHeight()
			}, 500)
		})
	},
	calculation: function(b, a, c, f, e) {
		$(document).on("keyup", a, function(a) {
			this.value = this.value.replace(/[^\d]/g, "");
			"" == this.value && (this.value = 1);
			parseInt($(this).val()) >= parseInt($(this).attr("data-max")) ?
				($(this).val($(this).attr("data-max")), $(c).addClass("notcurr"), $(b).removeClass("notcurr")) : 1 >= parseInt($(this).val()) ? ($(this).val(1), $(b).addClass("notcurr"), $(c).removeClass("notcurr")) : ($(c).removeClass("notcurr"), $(b).removeClass("notcurr"));
			if ($(this).parents(".global-cart").size()) {
				var h = $(this).closest("li"),
					g = h.find("input[type='checkbox']").attr("data-sn"),
					f = parseInt(h.find("input.buy-num").val()),
					l = $(".global-cart .cart-buy-price");
				$(window).width();
				$(".global-cart").offset();
				$(".cart-body").outerHeight();
				e && $.ajax({
					url: e.cartUpdate.ajaxUrl,
					type: e.cartUpdate.ajaxType,
					data: {
						cart_id: g,
						quantity: f
					},
					dataType: e.cartUpdate.ajaxDataType,
					timeout: e.cartUpdate.ajaxTimeout,
					success: function(a) {
						h.find(".price strong").empty().html("\uffe5" + a.data.single_total);
						l.html("\uffe5" + a.data.total.total)
					},
					error: function(a, b, c) {
						layer.msg("\u64cd\u4f5c\u5931\u8d25\uff01")
					}
				})
			}
			a.stopPropagation()
		});
		$(document).on("click", b, function() {
			if (!$(this).hasClass("notcurr")) {
				var d = $(this).closest(f),
					e = parseInt(d.find(a).val() ? d.find(a).val() :
						0);
				e--;
				if (1 >= e) return d.find(b).addClass("notcurr"), d.find(a).val(1), !1;
				d.find(b).removeClass("notcurr");
				d.find(c).removeClass("notcurr");
				d.find(a).val(e)
			}
		});
		$(document).on("click", c, function() {
			if (!$(this).hasClass("notcurr")) {
				var d = $(this).closest(f),
					e = parseInt(d.find(a).attr("data-max")),
					g = parseInt(d.find(a).val() ? d.find(a).val() : 0);
				g++;
				if (e && g >= e) return d.find(c).addClass("notcurr"), d.find(b).removeClass("notcurr"), d.find(a).val(e), !1;
				1 < g && (d.find(c).removeClass("notcurr"), d.find(b).removeClass("notcurr"));
				d.find(a).val(g)
			}
		})
	},
	checkAll: function(b, a) {
		var c = $(b).not(":disabled"),
			f = $(a);
		c.unbind("change");
		c.on("change", function() {
			if ($(this).is(":checked")) {
				var a = $(c).size();
				$(c + ":checked").size() >= a ? $(f).prop("checked", !0) : $(f).prop("checked", !1)
			} else f.prop("checked", !1)
		});
		f.change(function() {
			1 == $(this).prop("checked") ? c.prop("checked", !0) : c.prop("checked", !1)
		})
	},
	deleteCart: function(b) {
		var a = $(".global-cart .cart-buy-price");
		$(".global-cart .del-btn").on("click", function() {
			var c = $(".cart-content-list input[type='checkbox']:checked"),
				f = [];
			if (c.size()) {
				for (var e = 0; e < c.length; e++) f.push(parseInt(c.eq(e).attr("data-sn")));
				$.ajax({
					url: b.cartDelList.ajaxUrl,
					type: b.cartDelList.ajaxType,
					data: {
						cart_ids: f
					},
					dataType: b.cartDelList.ajaxDataType,
					timeout: b.cartDelList.ajaxTimeout,
					success: function(d) {
						c.closest("li").remove();
						a.html("\uffe5" + d.data.total.total);
						cartFunction.cartShopSize(b, d);
						$(".cart-content-list").children("li").size() ? cartFunction.cartOpen() : cartFunction.cartClose()
					},
					error: function(a, b, c) {
						layer.msg("\u64cd\u4f5c\u5931\u8d25\uff01")
					}
				})
			} else layer.msg("\u8bf7\u9009\u62e9\u8d2d\u7269\u8f66\u5546\u54c1\uff01")
		})
	},
	cartShopSize: function(b, a) {
		b.cartShopSize && a ? $(".global-cart .cart-list-count").empty().html(a.data.total.quantity) : $(".global-cart .cart-list-count").empty().html($(".cart-content-list li").size())
	}
};

$(function() {
	/*cartFunction.init({
                addCart:{//加入购物车接口
			    	ajaxUrl:"http://192.168.0.109/bld/index.php/index/cart/add_from_list.html",
			    },
			    specsName:{//获取规格名称
			    	ajaxUrl:"http://192.168.0.109/bld/index.php/index/cart/get_goods_specs_name.html",
			    },
			    specsItem:{//获取第一栏规格参数
			    	ajaxUrl:"http://192.168.0.109/bld/index.php/index/cart/select_specs_1.html",
			    },
			    specsInfo:{//获取第二栏规格参数
			    	ajaxUrl:"http://192.168.0.109/bld/index.php/index/cart/select_specs_2.html",
			    },
			    cartNumAdd:{//购物车加操作
			    	ajaxUrl:"http://192.168.0.109/bld/index.php/index/cart/goods_setInc.html",
			    },
			    cartNumReduce:{//购物车减操作
			    	ajaxUrl:"http://192.168.0.109/bld/index.php/index/cart/goods_setDec.html",
			    },
			    cartDelList:{//购物车删除操作
			    	ajaxUrl:"http://192.168.0.109/bld/index.php/index/cart/batch_drop.html",
			    },
			    cartUpdate:{//购物车删除操作
			    	ajaxUrl:"http://192.168.0.109//bld/index.php/index/cart/update_goodsnums.html",
			    }
	});*/
	//cartFunction.init();

	/**=======*/
	$(".wrapbox").css({
		"padding-bottom": $(".footer").outerHeight()
	});

	/*网站导航*/
	if ($(".nav-sblist-box").size()) {
		dorpdownInit(1);
		var b, c = 100;
		$(".menu-all-list>li").hoverIntent({
			interval: 0,
			over: function() {
				clearTimeout(b);
				$(this).addClass("hover").siblings("li").removeClass("hover");
				$(".menu-dorpdown-layer").show();
				dorpdownInit($(this).attr("data-index"));
			},
			timeout: 0,
			out: function() {
				var _$this = $(this);
				b = setTimeout(function() {
					$(".menu-dorpdown-layer").hide();
					_$this.removeClass("hover");
				}, c);
			}
		});
		$(".menu-dorpdown-layer").hoverIntent({
			interval: 0,
			over: function() {
				clearTimeout(b);
				$(".menu-dorpdown-layer").show();
			},
			timeout: 0,
			out: function() {
				b = setTimeout(function() {
					$(".menu-dorpdown-layer").hide();
					$(".menu-all-list>li").removeClass("hover");
				}, c);
			}
		});
	}
	//全局导航
	var $sblistMenu = $(".nav-sblist-box");
	if (!$sblistMenu.hasClass("menu-slideDown-bar") || !$sblistMenu.hasClass("menu-notshow")) {
		//$sblistMenu.find(".shop-all-title b").removeClass().addClass("icon-menu-up");
		$sblistMenu.find(".shop-all-title b").remove();
	}
	$(".menu-slideDown-bar").hover(function() {
		$(this).find(".menu-all").show();
		$(this).find(".shop-all-title b").removeClass().addClass("icon-menu-up");
	}, function() {
		$(this).find(".menu-all").hide();
		$(this).find(".shop-all-title b").removeClass().addClass("icon-menu-down");
	});

	//首页现实团购
	$("ul.tuan-head-list li:last").find("a").css("border-right", "0px");


	//返回顶部
	$(".goto-top").click(function() {
		$("html,body").animate({
			scrollTop: 0
		}, "fast");
	});
	//滑块，显示菜单
	$(window).scroll(function() {
		var winTop = $(document).scrollTop();
		if (winTop >= 500) {
			//浮动菜单
			$(".gotoLink").fadeIn("fast");
			$(".gotoLink").each(function() {
				$(this).css({
					top: "50%",
					marginTop: -$(this).outerHeight() / 2,
					zIndex: 888
				});
			});
		} else {
			$(".gotoLink").fadeOut("fast");
		}
	});

	//会员中心菜单折叠
	$(".user-menu-list li").each(function() {
		if ($(this).children("ul").size()) {
			$(this).children("a").append('<span class="arrow"></span>');
		}
	});
	//店铺中心 左边菜单
	$(".menu-type-list dd.current").find("ul").show();
	var $menuTypelist = $(".menu-type-list dd>a");
	$menuTypelist.on("click", function() {
		$(this).parent().addClass("current").siblings("dd").removeClass("current").find().removeClass("list-selected");
		$(this).parent().addClass("current").siblings("dd").find("ul").slideUp("method");
		$(this).parent().find("ul").slideDown("method");
		if ($(this).parent().find("ul").size() > 0) {
			return false;
		};
	});

});
var dorpdownInit = function(nbIndex) {
	//初始化隐藏
	if ($(".menu-all").size()) {
		var nbTop_x = $(".menu-all").offset().top < $(document).scrollTop() ? $(document).scrollTop() - $(".menu-all").offset().top : 0;
		if (nbIndex) {
			$(".menu-dorpdown-cont").children("div").hide();
			$(".menu-dorpdown-cont").children(".menu-item-" + nbIndex).show();
			$(".menu-dorpdown-layer").css({
				"top": nbTop_x + "px"
			});
		} else {
			$(".menu-dorpdown-cont").children("div").hide();
		}
	}
}