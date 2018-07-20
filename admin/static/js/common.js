function saving(obj){	
	obj.value = "保存中...";
	if(obj.disabled==true){
		obj.disabled=false;
		
	}else{
		obj.disabled = "true";
	}
}
/*收起和隐藏分类列表*/
function hide_child($pid){
	var objid	= $('tr[pid=class_'+$pid+']');
	if(objid.length>0){
		objid.each(function(){
			var selfId	= $(this).attr('id').split('_')[1];
			if($(this).css('display')=='none'){
				$(this).show();
			}else{
//				$(this).parents('tr[id=class_'+$pid+']');
				$('tr[pid=class_'+selfId+']').show();
				$(this).hide();
			}
			hide_child(selfId);
		});
		
		
	}
}
$(document).ready(function(){
	/*分类点击*/
	$('tr[id^=class_][pid^=class_]').each(function(){
		var id= $(this).attr('id').split('_')[1];
//		var pid= $(this).attr('pid').split('_')[1];
		var objid	= $('tr[pid=class_'+id+']');
		if(objid.length>0){
			$(this).find('td:eq(0) a').append('<font color="red" size="3"> -</font>');
			$(this).find('td:eq(0)>a').click(function(){
				var id= $(this).parents('tr').attr('id').split('_')[1];
				hide_child(id);
				/*var objid	= $('tr[pid=class_'+id+']');
				if(objid.css('display')=='none'){
					objid.show();
				}else{
					objid.hide();
				}*/
				return false;
			});
		}
	});
	
	if ($.browser.msie && $.browser.version=="6.0"){
		if($("tr")){
			$("tr").bind("mouseover",function(){
				$(this).addClass("hover");
				
			});
			$("tr").bind("mouseout",function(){
				$(this).removeClass("hover");
				
			});
			
		}	
	}

	$.fn.extend({
		maxlength:function(){
			var me  = $(this),
				Max = me.attr("maxlength"),
				curr = me.val().length,
				lengthcount = "",
				init = function(){

					lengthcount = $("<p><span>"+curr+"</span>/<span>"+Max+"</span></p>");
					me.parent().css({
						position:"relative",
					}).append(lengthcount.css({
						position:"absolute",
						bottom:"5px",
						right:"11%"
					}));
					me.keyup(function(){
						curr  = me.val().length;
						if(curr>Max){
							curr = Max;
							var val = me.val().substr(0,Max);
							me.val(val);
						}
						var currHTML = "<p><span>"+curr+"</span>/<span>"+Max+"</span></p>";
						lengthcount.html(currHTML);
					});
				};
			init();
		},
	});
	if($(".maxlength").length>=1){
		$(".maxlength").maxlength();
	}
	

	$('*[class^=upload_]').click(function(){
		var cls	= $(this).attr('class');
		var input_id	= cls.split('_')[1];
		var obj_val	= $('#'+input_id).val();
		var parn	= /\s/;
		var allreplace	= '0';
		if(parn.test(obj_val)){
			allreplace	= 1;
			var tiqu= obj_val.match(/\/.*(jpg|gif|png|ico)/i);
			obj_val	= tiqu[0];
		}
			var vala	= obj_val.split('/');
			var dir	= vala[5];
			var new_name	= vala[vala.length-1];
			new_name	= new_name.split('?')[0];
			if(new_name.split('.')[1]){
				var tem	= new_name.split('.')[1].toLowerCase();
				if(tem!='jpg' && tem!='png' && tem!='gif'){
					new_name='';
				}
			}
		
		if(dir==undefined||new_name==undefined||dir.length!=8||dir==''||new_name==''){
			dir	='';
			new_name='';
		}
		var iHeight = 220;
		var iWidth = 350;
		 //获得窗口的垂直位置
	    var iTop = (window.screen.availHeight-30-iHeight)/2;        
	    //获得窗口的水平位置
	    var iLeft = (window.screen.availWidth-10-iWidth)/2;   
		childWindow = window.open("./?s=upload&a=plug_upload&new_name="+new_name+"&dir="+dir+"&toobj="+input_id+"&allreplace="+allreplace,"Plug_upload","top="+iTop+",left="+iLeft+",width="+iWidth+",height="+iHeight+",menubar=0,scrollbars=1, resizable=1,status=1,titlebar=0,toolbar=0,location=1");
//		childWindow = window.open("./?c=upload&a=plug_upload&dir="+dir+"&obj=remark&new_name="+new_name+"&fun=fill_img_attr","Plug_upload","top="+iTop+",left="+iLeft+",width="+iWidth+",height="+iHeight+",menubar=0,scrollbars=1, resizable=1,status=1,titlebar=0,toolbar=0,location=1");
		return false;
	});
	
	$('*[upload^=upload_]').click(function(){
		
		var cls	= $(this).attr('upload');
		var input_id	= cls.split('_')[1];
		var obj_val	= $('#'+input_id).val();
		var parn	= /\s/;
		var allreplace	= '0';
		if(parn.test(obj_val)){
			allreplace	= 1;
			var tiqu= obj_val.match(/\/.*(jpg|gif|png|ico)/i);
			obj_val	= tiqu[0];
		}
			var vala = obj_val.split('/');
			var dir	= vala[5];
			var new_name	= vala[vala.length-1];
			new_name	= new_name.split('?')[0];
			if(new_name.split('.')[1]){
				var tem	= new_name.split('.')[1].toLowerCase();
				if(tem!='jpg' && tem!='png' && tem!='gif'){
					new_name='';
				}
			}
		
		if(dir==undefined||new_name==undefined||dir.length!=8||dir==''||new_name==''){
			dir	='';
			new_name='';
		}
		var iHeight = 320;
		var iWidth = 450;
		 //获得窗口的垂直位置
	    var iTop = (window.screen.availHeight-30-iHeight)/2;        
	    //获得窗口的水平位置
	    var iLeft = (window.screen.availWidth-10-iWidth)/2;   
		childWindow = window.open("./?s=upload&a=plug_upload&new_name="+new_name+"&dir="+dir+"&toobj="+input_id+"&allreplace="+allreplace,"Plug_upload","top="+iTop+",left="+iLeft+",width="+iWidth+",height="+iHeight+",menubar=0,scrollbars=1, resizable=1,status=1,titlebar=0,toolbar=0,location=1");
//		childWindow = window.open("./?c=upload&a=plug_upload&dir="+dir+"&obj=remark&new_name="+new_name+"&fun=fill_img_attr","Plug_upload","top="+iTop+",left="+iLeft+",width="+iWidth+",height="+iHeight+",menubar=0,scrollbars=1, resizable=1,status=1,titlebar=0,toolbar=0,location=1");
		return false;
	});
});


$(function(){
	$('.update_order').each(function(){

		$(this).Aupdate('./?s=update');
	});
	var view_box=null;
	var view_sign=0;
	$('.vpic').mouseover(function(e){
		e.stopPropagation();
		var x,y;
		var values	= $(this).val();
		if(view_box === null){
			view_box = $('<div id="view_box"><div style="right:0px;top:0px;color:red;position:absolute;cursor:pointer;background-color:#cccccc;width:100%;height:15px;line-height:10px;text-align:right;font-size:16px;" onclick="$(\'#view_box\').hide(\'slow\');"><div id="pic_info" style="float:left"></div>&nbsp;&nbsp;&nbsp;&nbsp;x</div><div class="img" style="padding-top:15px;"></div></div>');
			
			view_box.css({"position":"absolute","border":"1px solid #999999"});
			$('.wrap').prepend(view_box);
		}
		view_box.show('slow');
		var reg	= /^http/i;
		if(!reg.test(values)){
			values	= "http://sys.everclose.cn"+values;
		}
		$img	= new Image();
		$img.src=values;
		$img.onload=function(){
			$("#pic_info").html(""+this.width+"*"+this.height);
		};
		view_box.find(".img").html("<img src='"+values+"' />");
		var e = e||window.event;
		//x=e.clientX+document.body.scrollLeft+document.documentElement.scrollLeft+10;
		x=450;
		y=e.clientY+document.body.scrollTop+document.documentElement.scrollTop;
		if(view_sign==0){
			view_sign=1;
			view_box.animate({"left":x+"px","top":y+"px"},'normal','swing',function(){view_sign=0;});
		}else{
			return false;
		}
		
		//view_box.css({"left":x+"px","top":y+"px"});
		//alert($(this).val());
	});
	if($("#cid").html()!==null){
		var cid	= $("#cid").val();
		$("#cid").change(function(){
			var cid	= $("#cid").val();
			showfield(cid);
		});
		showfield(cid);
	}
});
function showfield(cid){
	if(Fields && !cid){
		for(var i=0;i<Fields.length;i++){
			$("."+Fields[i]).show();
		}
	}else if(cid){
		$.ajax({
			cache: false,
			url:'/?s=Classes&a=getClassField',
			type: "POST",
			dataType: "json",
			timeout:'30000',
//			async: false,
			data: {
				cid		:cid
			},
			success: function(result){
				if(result.error ==0){
					if(result.data){
						$(".hiden").hide();
						for(var i=0;i<result.data.length;i++){
							$("."+result.data[i]).show();
						}
					}else{
						alert('error of data');
					};
				}
				else{
					alert(result.msg);
				}
			}
		});
	}
}
/*文字点击变为text框,鼠标离开后自动ajax提交更新 
 * info属性eg: info="更新的主键id|表名|更新的字段名|主键id字段名"
 * add by mwt*/
jQuery.fn.extend({
	  Aupdate: function(php) {
	  	this.html('<font>'+this.html()+'</font>');
	  	this.click(function(){
		  		$(this).children('font').parent().html($('<input type="text" value="'+$(this).children('font').html()+'" onblur="$(this).reback(\''+php+'\')"/>'));
		  	});
	  	return this; 
	  },
	reback: function(php)
	{
		var _info = $(this).parent().attr('info');
		var info = _info.split('|');
		if(info.length<2){alert('info error');return false;}
		var _v	= this.val();
		this.parent().html('<font>'+_v+'</font>');
		
		/*回调ajax函数更新数据*/
		$.ajax({
			cache: false,
			url:php,
			type: "POST",
//			dataType: "json",
			dataType: "text",
			timeout:'30000',
//			async: false,
			data: {
				id		:info[0],
				tb		:info[1],
				fi		:info[2],
				fid	:(info[3]==undefined)?'':info[3],
				value	:_v
			},
			success: function(result){
				if(result ==1){
					if(confirm('已经提交成功,是否刷新?')){
						window.location.reload();
					};
				}
				else{
					alert('更新失败,请检查登录是否过期,或网络是否中断。');
				}
			}
		});
	},
	});
function get_arm_sound(table,id){
	var phpurl	= "";
	$.ajax({
		cache: false,
		url:phpurl,
		type: "POST",
//		dataType: "json",
		dataType: "text",
		timeout:'30000',
//		async: false,
		data: {
			id		:id,
			tb		:table
		},
		success: function(result){
			if(result ==1){
				if(confirm('已经提交成功,是否刷新?')){
					window.location.reload();
				};
			}
			else{
				alert('更新失败,请检查登录是否过期,或网络是否中断。');
			}
		}
	});
}
function $_get(key) {
	var Arg,arg,i;
	Arg = document.location.search.split("?");
	if(Arg[1]) {
		Arg=Arg[1].split("&");
	}
	for(i=0; i < Arg.length; i++) {
		var arg = Arg[i].split("=");
		if(key == arg[0]) {
			return unescape(arg[1]);
		}
	}
	return "";
}