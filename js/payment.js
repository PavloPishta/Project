 var pEnum = 0;
 var payment = {
   metodbox: function(){
    viiBox.start();
	$.post('/index.php?go=balance&act=metodbox', function(d){
	  viiBox.win('metodbox', d);
	});
  },
   mt_invite: function(){
    viiBox.start();
	$.post('/index.php?go=balance&act=metodbox_invite', function(d){
	  viiBox.win('mt_invite', d);
	});
  },
  box: function(){
    viiBox.start();
	$.post('/index.php?go=balance&act=metodbox_rubcmc', function(d){
	  viiBox.win('metodbox_rubcmc', d);
	});
  },
  box_two: function(){
    viiBox.start();
	$.post('/index.php?go=balance&act=metodbox_rub', function(d){
	  viiBox.win('metodbox_rub', d);
	  $('#cost_balance').focus();
	});
  },
  operator: function(v){
	var check = $('#'+v).length;
	if(check){
	  $('#payment_oper').html($('#'+v).html()).attr('disabled', 0);
	} else {
	  $('#payment_oper').html('<option value="0"></option>').attr('disabled', 'disabled');
	}
	$('#payment_cost').html('<option value="0"></option>').attr('disabled', 'disabled');
	$('#smsblock').hide();
  },
  cost: function(v){
    var check = $('#cost_'+v).length;
	if(check){
	  $('#payment_cost').html($('#cost_'+v).html()).attr('disabled', 0);
	} else {
	  $('#payment_cost').html('<option value="0"></option>').attr('disabled', 'disabled');
	}
	$('#smsblock').hide();
  },
  number: function(v, t){
    var v = v.split('|');
    if(v[0] != 0){
	  $('#smsblock').show();
	  $('#smsnumber').text(v[0]);
	  if(v[1]) $('#smspref').text(v[1]);
	  else $('#smspref').text('');
	} else
	  $('#smsblock').hide();
  },
  update1: function(){
    var pr = parseInt($('#cost_balance').val());
	if(!isNaN(pr)) $('#cost_balance').val(parseInt($('#cost_balance').val()));
	else $('#cost_balance').val('');
	var num = $('#cost_balance').val() * $('#cost').val();
	var res = ( $('#balance').val() - num );
	$('#num').text( res );
	if(!$('#cost_balance').val()) $('#num').text( $('#balance').val() );
	else if(res < 0) $('#num').text('недостаточно');
  },
  send1: function(){
    var num = $('#cost_balance').val();
	var num_2 = $('#cost_balance').val() * $('#cost').val();
	var res = $('#balance').val() - num_2;
	var rub2 = $('#balance').val() - num_2;
	if(pEnum > 10){
	  alert('Я тебе голову сломаю!');
	  pEnum = 0;
	}
	if(res <= 0) res = 999999999999;
	if(num != 0 && $('#balance').val() >= res){
	  butloading('saverate', 50, 'disabled', '');
      $.post('/index.php?go=balance&act=ok_payment', {num: num}, function(d){
	    if(d == 1){
		  addAllErr('Пополните баланс для покупки.', 3300);
		  return false;
		}
	    $('#rub2').text(rub2);
	    $('#num2').text(parseInt($('#num2').text()) + parseInt(num));
        viiBox.clos('metodbox_rub', 1);
		Box.Info('msg_info', 'Приобретение голосов', 'Голоса успешно начислены' , 300, 1600);
      });	
	} else {
	  setErrorInputMsg('cost_balance');
	  pEnum++;
	}
  },

  addbox: function(){
    viiBox.start();
	$.post('/index.php?go=balance&act=payment_2', function(d){
	  viiBox.win('payment_2', d);
	});
  },
  nowork_mb:function(){
   $('#nowork').show();
  },
  save: function(u){
  	var numus = parseInt($('#num_balance').text()) - parseInt($('#payment_num').val());
	var add = $('#payment_num').val();
	var upage = $('#upage').val();
	var cnt = $('#cnt').val();
	var userid = $('#userid').val();
	if(parseInt($('#balance').val()) < parseInt($('#payment_num').val())){
	  setErrorInputMsg('payment_num');
	  return false;
	}
if(add != 0){
	if(parseInt($('#cnt').val()) >= parseInt($('#upage').val())){
	if(upage >= 1){
	if(userid != upage){
	  butloading('saverate', 50, 'disabled', '');
	  $.post('/index.php?go=balance&act=payment_2', {for_user_id: upage, num: add}, function(d){
	  $('#num_balance').text(numus);
		viiBox.clos('payment_2', 1);
		Box.Info('msg_info', 'Передача голосов', 'Голоса успешно переданы.' , 300, 1600);
	  });
	  }
	  else
		Box.Info('msg_info', 'Ошибка', 'Нельзя передавать голоса самому себе.' , 300, 1600);
}
	else
	setErrorInputMsg('upage');
}
	else
	setErrorInputMsg('upage');
}
	else
	setErrorInputMsg('payment_num');

  },
  username:function(){
   $('#num').text('недостаточно');
  },

  update: function(){
    var add = $('#payment_num').val();
	var new_rate = $('#balance').val() - add;
	var pr = parseInt(add);
	if(!isNaN(pr)) $('#payment_num').val(parseInt(add));
	else $('#payment_num').val('');
	if(add && new_rate >= 0){
	  $('#num').text(new_rate);
	  $('#rt').show();
	} else if(new_rate <= 0 || $('#balance').val() <= 0){
	  $('#num').text('недостаточно');
	  $('#rt').hide();
	} else {
	  $('#rt').show();
	  $('#num').text($('#balance').val());
	}
  }
}

   //Вычесляем юзера по id
	var payments = {
  checkPaymentUser: function(){
	var upage = $('#upage').val();
	var pattern = new RegExp(/^[0-9]+$/);
	if(pattern.test(upage)){
		$.post('/index.php?go=balance&act=checkPaymentUser', {id: upage}, function(d){
		d = d.split('|');
	if(d[0]){
	if(d[1])
		$('#feedimg').attr('src', '/uploads/users/'+upage+'/50_'+d[1]);
	else
		$('#feedimg').attr('src', '/images/no_ava_50.png');

	} else {
	  	setErrorInputMsg('upage');
		$('#feedimg').attr('src', '/images/no_ava_50.png');
			}
			});
	} else
		$('#feedimg').attr('src', '/images/no_ava_50.png');
  }

}