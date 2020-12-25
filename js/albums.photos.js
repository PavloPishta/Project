$(document).ready(function(){
	var aid = $('#aid').val();
	Xajax = new AjaxUpload('upload', {
		action: '/index.php?go=albums&act=upload&aid='+aid,
		name: 'uploadfile',
		onSubmit: function (file, ext) {
			if (!(ext && /^(jpg|png|jpeg|gif|jpe)$/.test(ext))) {
				Box.Info('load_photo_er', lang_dd2f_no, lang_bad_format, 400);
				return false;
			}
			Page.None_loading('start');
		},
		onComplete: function (file, response){
			if(response == 'max_img'){
				Box.Info('load_photo_er2', lang_dd2f_no, lang_max_imgs, 340);
				Page.None_loading('stop');
				return false
			}
			
			if(response == 'big_size'){
				Box.Info('load_photo_er2', lang_dd2f_no, lang_max_size, 250);
				Page.None_loading('stop');
				return false
			}
				
			if(response == 'hacking'){
				return false
			} else {
				response = response.split('|||');
				$('<span id="photo_'+response[0]+'"></span>').appendTo('#photos').html('<div id="cover_'+response[0]+'" class="covers photos_news_view"><a href="/photo'+response[2]+'_'+response[0]+'_sec=loaded" onClick="Photo.Show(this.href); return false"><div class="albums_cover_new"><span id="count_img"><img src="'+response[1]+'" alt="" /></span></div></a><div style="float:left;"><div class="albums_name" style="color:#45688E;padding-bottom:5px;"><b>'+lang_albums_add_photo+'</b></div><textarea class="inpst" id="descr_'+response[0]+'" style="width:298px;height:73px;"></textarea><div class="clear"></div></div><div class="l_pppho"><ul><a href="/" onClick="SetNewCover(\''+response[0]+'\'); return false;" id="cover_link_'+response[0]+'"><li class="li_nav"><span class="left_label inl_bl">'+lang_albums_set_cover+'</span></li></a><a href="/" onClick="AlbumDeletePhoto(\''+response[0]+'\'); return false;"><li class="li_nav"><span class="left_label inl_bl">'+lang_albums_del_photo+'</span></li></a><a href="/" onClick="PhotoSaveDescr(\''+response[0]+'\'); return false;"><li class="li_nav"><span class="left_label inl_bl">'+lang_albums_save_descr+'</span></li></a><ul></div><div class="clear"></div></div>');
				
				var count_img = $('#count_img img').size();
				if(count_img == 1)
					$('#l_text').show();
					$('#r_text').hide();

				$('body, html').animate({scrollTop: 99999}, 250);
				Page.None_loading('stop');
			}
		}
	});
});
function AlbumDeletePhoto(i){
	$.get('/index.php?go=albums&act=del_photo', {id: i}, function(){
		$('#photo_'+i).remove();
		var count_img = $('#count_img img').size();
		if(count_img < 1)
			$('#l_text').hide();
			$('#r_text').show();
		Box.Info('add_fave_err_'+i, lang_dd2f_no, lang_info_created, 400);
	});
}
function SetNewCover(i){
	$.get('/index.php?go=albums&act=set_cover', {id: i}, function(){
		$('.covers').css('background', '#fff');
		$('#cover_'+i).css('background', '#f6f9fb');
		$('.cover_links').show();
		$('#cover_link_'+i).hide();
		Box.Info('add_fave_err_'+i, lang_dd2f_no, lang_info_created, 400);
	});
}
function PhotoSaveDescr(i){
	var descr = $('#descr_'+i).val();
	$.post('/index.php?go=albums&act=save_descr', {id: i, descr: descr}, function(d){
		Box.Info('add_fave_err_'+i, lang_dd2f_no, lang_info_created, 400);
	});
}