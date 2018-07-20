var $addclass = $html_div ="";
$.fn.extend({
	addclass:function(o){
		this.click(function(){
			var _this = this;
			var offset = $(this).offset();
			var a = part(this.value);
			$addclass = $("<div id='addclass' style='position:absolute;top:"+offset.top+"px;left:"+offset.left+"px'></div>").appendTo(o.append);
			//.location.href = "/?s=Classes&a=getclass&val="+this.value;
			if(o.html){
				$html = $("<p>"+o.html+"</p>").appendTo($addclass);
				$html.click(o.fun($addclass));
			}
			$.getJSON(o.url+this.value,function(data){
				if(data.error==0){
					var html =data.data;
					$html_div = $("<div>"+html+"</div>").appendTo($addclass);
				}else{
					$("<div>系统错误请重试</div>").appendTo($addclass);
				}
				$("<button>确定</button>").appendTo($addclass).classend(_this);
				$("<button>取消</button>").appendTo($addclass).click(function(){
					$addclass.remove();
				});
			});
		});
	},
	classend:function(o){
		this.click(function(){
			var $checkbox = $html_div.find('input');
			var l = $checkbox.length;
			var arr =[];
			for(i=0;i<l;i++){
				if($checkbox.eq(i).attr('checked')){
					arr.push($checkbox.eq(i).val());
				}
			}
			o.value = arr.join(",");
			$addclass.remove();
		});
	},
	changeclass:function(o){
		if($addclass){
			$addclass.remove();
		}
		var _this = this;
		var offset = $(this).offset();
		var a = part($(this).attr('title'));
		$addclass = $("<div id='addclass' style='position:absolute;top:"+(offset.top+24)+"px;right:0px'></div>").appendTo(o.append);
		$.getJSON(o.url+a,function(data){
			if(data.error==0){
				var html =data.data;
				$html_div = $("<div>"+html+"</div>").appendTo($addclass);
			}else{
				$("<div>系统错误请重试</div>").appendTo($addclass);
			}
			$("<button>确定</button>").appendTo($addclass).classend2(_this);
			$("<button>取消</button>").appendTo($addclass).click(function(){
				$addclass.remove();
			});
		});
	},
	classend2:function(o){
		this.click(function(){
			var $checkbox = $html_div.find('input');
			var l = $checkbox.length;
			var arr =[];
			for(i=0;i<l;i++){
				if($checkbox.eq(i).attr('checked')){
					arr.push($checkbox.eq(i).val());
				}
			}
			var id = o.attr('data-id');
			var val = arr.join(",");
			$.getJSON("/?s=Classes&a=changeclass&id="+id+"&val="+val,function(data){
				if(data.error == 0){
					o.attr('title',val);
					o.text(val);
					$addclass.remove();
				}else{
					alert("修改失败！");
				}
			});
			
			
		});
	},
});
function part(str){
	 return str.split(",");
}