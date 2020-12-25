/* 
	Appointment: Фон
	File: fon.php
	Author: Александр Конотопский
	Url: http://www.facemy.org
 
*/
var fon = {
  addbox: function(f){
    $('.js_titleRemove').remove();
    viiBox.start();
	$.post('/index.php?go=fon', {for_user_id: f}, function(d){
	  viiBox.win('fon_mouse', d);
	});
  }
}
var fon_facemy = {
	init: function(i, hi){
		$('#fon_facemy_img').attr('src', i);
		({
			stop: function(){
				$('.fon_facemy_add').show();
			},
			drag: function(event, ui){
				var d = ui.position.top;
				$('.fon_facemy_add').hide();
				if(d >= 0){
					$('#fon_facemy_restart').html('<div style="width:400px;height:'+hi+'px;position:relative;top:0px;z-index:5"><img src="'+i+'" width="400" id="fon_facemy_img" /></div>');
					$('.fon_facemy_add').show();
					fon_facemy.init(i, hi);
				}
				h = parseInt('-'+(hi-194));
				if(d <= h){
					$('#fon_facemy_restart').html('<div style="width:400px;height:'+hi+'px;position:relative;top:'+h+'px;z-index:5"><img src="'+i+'" width="400" id="fon_facemy_img" /></div>');
					$('.fon_facemy_add').show();
					fon_facemy.init(i, hi);
				}
			}
		});
	},
	reload_facemy: function(){window.location.reload();},
	del: function(user_id){
		$('.fon_facemy_add').hide();
		$('#fon_facemy_img').attr('src', '');
		$('#fon_facemy_restart').html('');
		if(user_id) $.post('/index.php?go=fon&act=del_fon&id='+user_id);
		else $.post('/index.php?go=fon&act=del_fon'); window.location.reload();
	}
}