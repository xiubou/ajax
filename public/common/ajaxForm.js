(function($){
	/*
	 * Ajax表单提交 
	 * @param {function} callback  提交后的函数
	 */
	$.fn.ajaxForm = function(callback){
		var thisObj = this;
		var url = thisObj.attr("action"); //获取提交的URL地址
		var token = $.getCSRFToken(); //获取令牌
		thisObj.append('<input type="hidden" name="token" value="'+token+'" />'); //将令牌追加到表单中
		thisObj.submit(function(e){
			e.preventDefault(); //阻止表单默认提交动作
			var data = thisObj.serializeArray(); //获取表单数据
			$.ajaxPostData(url,data,callback); //Ajax提交数据
		});
	};
	/*
	 * Ajax提交数据
	 * @param {string} url 提交的URL地址
	 * @param {mixed} before 提交前的函数，或提交的JSON数据，false表示阻止提交
	 * @param {function} after  提交后的函数
	 */
	$.fn.ajaxButton = function(url,before,after){
		this.click(function(){
			//获取要提交的数据。如果是函数则调用取得返回值，如果不是则直接当做数据
			var data = $.isFunction(before) ? before.call(this) : before;
			//当数据是false时阻止提交
			if (false === data) {return false;}
			data["token"] = $.getCSRFToken(); //获取令牌
			$.ajaxPostData(url,data,after); //Ajax提交数据
			return false; //阻止默认动作
		});
	};
	/*
	 * Ajax提交数据并自动处理
	 * @param {string} url 目标URL地址
	 * @param {json} data 发送的数据
	 * @param {function} callback 发送后的回调函数
	 */
	$.ajaxPostData = function(url,data,callback) {
		$.post(url,data,function(data){
			//执行跳转
			if (data.target!=="") {location.href=data.target;}
			//执行回调函数
			if (callback!==undefined) {callback(data);}
			//显示信息提示
			if (data.msg!=="") {$.showTip(data.msg);}
		},"JSON");
	};
	/*
	 * 显示提示
	 * @param {string} msg 消息文本
	 */
	$.showTip = function(msg){
		$(".tip").remove(); //如果已经显示则隐藏
		var $obj = $('<div class="tip"><div class="tip-wrap">'+msg+'</div></div>');
		$("body").append($obj);
		//在屏幕中央显示
		$obj.css("margin-left","-"+($obj.width()/2)+"px");
		$obj.css("margin-top","-"+($obj.height()/2)+"px");
		//单击后隐藏
		$("body").one("click",function(){
			$obj.fadeOut(200,function(){ //以淡出动画效果隐藏
				$obj.remove(); //彻底隐藏后移除元素
			});
		});
	};
	/*
	 * 获取CSRF令牌
	 * @returns {string} 令牌值
	 */
	$.getCSRFToken = function(){
		var $obj = $("meta[name=csrf_token]");
		return ($obj.length>0) ? $obj.attr("content") : "";
	};
})(jQuery);