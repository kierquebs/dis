var modalID = 'rwindowModal';

(function () {	
setDatatbl('#comment-tbl');
datePick();
setModal(modalID, 'Releasing Order', 'SET');
searchBox();
})();

function searchBox(){
	$('#search-form button[type=submit]').click(function(){
		$searchTxt = '';
		$search = $('#search-form input[name="search"]').val();
			if($search != '') $searchTxt = '&search='+$search;	
		actionFilter($searchTxt);
	});
	
	$('.alert-me').click(function(e){
		alertMe = true;
		alert('ALERT CLIENT!');		
		location.href="rwindow/alert_me";
	});
	
	$('.alert-voice').click(function(e){
		$set = $('.alert-voice').attr('data-id');
		console.log($set);
		alert('VOICE ALERT TURN '+($set == 0 ? 'OFF' : 'ON'));		
		location.href="rwindow/alert_voice/"+$set;
	});
	
}
/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "GET",
	url: "rwindow/get_rwindow?page="+page+search,
	dataType: "json",
	success: function (data) {
		myTable.rows().remove().draw();
		$('#'+tblID+'_paginate').remove();
		if(data.total != 0){
		$jsonReturn = data.result;
		if(data.offset > 0) $i = data.offset + 1;
		$w2 = $w1 = 0; 
		$.each(data.result, function(key, value) {				
		$reID = value.reorder_id;
		$orderID = value.order_id;
		$windowNum = value.window;
		var result = [];
			result.push($orderID); //ORDER ID
			result.push(value.company_name); //COMPANY NAME
			result.push(value.contact); //CONTACT PERSON
			result.push(value.date_received); //DATE RECEIVED
			result.push(value.date_release); //DATE RELEASE
			result.push(''); //SERVE TO
		if($windowNum == 1) $w1 = $windowNum;
		if($windowNum == 2) $w2 = $windowNum;
		
		$formDiv = ' --- ';
		if(value.btn_access == true){
			$formDiv = $('<div></div>').addClass('serve-window btn-group').attr({'data-toggle':"buttons"});	
			$win1 = $('<label></label>').addClass('window btn btn-info w1').text('W 1');
					if($windowNum == 1)	$win1.addClass('active');
				$($win1).append( $('<input />').attr({'type': 'radio', 'name': 'window', 'id':1, 'autocomplete':'off'}) );
			$win2 = $('<label></label>').addClass('window btn btn-info w2').text('W 2');
					if($windowNum == 2)	$win2.addClass('active');
				$($win2).append( $('<input />').attr({'type': 'radio', 'name': 'window', 'id':2, 'autocomplete':'off'}) );			
			$btn = $('<button></button>').addClass('serve btn btn-badge').attr({'value': $reID, 'status': value.sstatus}).text(value.nstatus);
				if(value.sstatus == 2) $btn.removeClass('btn-badge').addClass('btn-warning');
				if($windowNum == 0) $btn.addClass('hide');
			$($formDiv).append($win1).append($win2).append($btn);
		}
		
			var newRow = myTable.row.add(result).draw().node(); 
				$(newRow).addClass('bin-row').attr({'data-id': $reID, 'data-order':$orderID})	
				.find('td:nth-child(1)').attr({'width': 150}).end()
				.find('td:nth-child(2)').addClass('company').end()
				.find('td:nth-child(3)').addClass('company').end()
				.find('td:nth-child(4)').attr({'width': 200}).end()
				.find('td:nth-child(5)').attr({'width': 200}).end()
				.find('td:nth-child(6)').attr({'width': 200}).append($formDiv).end()
		});	
		if($w1 == 1) $('.serve-window .w1').not('.w1.active').addClass('disabled');
		if($w2 == 2) $('.serve-window .w2').not('.w2.active').addClass('disabled'); 
		pagination(data.total, data.per_page, false);
		rwindowBTN();
		}else activateLinks();		
		/*windows served*/
		$('.now-serve p.window-1').attr({'id':data.w1_id}).html(data.w1_cname);
			$('.now-serve p.window-1').next('span').html(data.w1_status);
		$('.now-serve p.window-2').attr({'id':data.w2_id}).html(data.w2_cname);
			$('.now-serve p.window-2').next('span').html(data.w2_status);
	}
	});
}

function rwindowBTN(){	
	$('.serve-window .window').attr({'data-toggle':'modal' , 'data-target':'#'+modalID});
	$('.serve-window .window').click(function(e){
		$me = $(e.currentTarget);
		if($me.hasClass('disabled')) e.stopPropagation();
		else{
			$window = $me.find('input[name="window"]').attr('id');
				$orderID = $me.closest('tr').attr('data-order');
				$dataID = $me.closest('tr').attr('data-id');
			$('.modal .modal-body').html("SERVE ORDER: "+$orderID+' TO WINDOW '+$window);
			$('.modal .modal-btn').attr({'data-window': $window,  'data-id':$dataID});
			$('.modal .modal-cancel').remove();
			
			if($('.now-serve').find('.window-'+$window).attr('id') == $dataID){
				$('.modal .modal-btn').after('<button type="button" class="modal-cancel btn btn-danger" data-dismiss="modal">CANCEL</button>');
			}
			modalBTN();
			$('.serve-window .w'+$window).not($(this)).addClass('disabled');
		}
	});	
	$('.serve-window .serve').click(function(e){	
		$dataID = $(e.currentTarget).val();
		$restatus = $(e.currentTarget).attr('status');	
		$re = $(e.currentTarget).closest('tr[data-id='+$dataID+']');
		$.ajax({
			type: 'POST',
			url: 'rwindow/serve_window',  
			data: { transid: $dataID, restatus: $restatus},	
			error: function() {
				failureCallback();
			},
			success: function(data) {
				if(data.sstatus == 1) {
					$re.remove();
					$windowID = $re.find('.window.active').find('input[name=window]').attr('id');
					$('.now-serve').find('.window-'+$windowID).html('---');
				}else if(data.sstatus == 2) $(e.currentTarget).attr('status', 1).removeClass('btn-badge').addClass('btn-warning').text('SERVING');

				PopulateItemsTable();
			},
			dataType: 'json'
		});
	});

}
function modalBTN(){	
	$('.modal-cancel').click(function(e){
		$windowID = $('.modal .modal-btn').attr('data-window');
		$dataID = $('.modal .modal-btn').attr('data-id');
		$companyName = $('#comment-tbl').find('tr[data-id='+$dataID+']').find('td.company').html();	
		$prevServe = $('.now-serve').find('.window-'+$windowID);
			$prevServe.html(' --- ');$prevServe.attr('id', '');
		$('tr[data-id='+$dataID+']').find('.serve-window .window').removeClass('active');
		$('tr[data-id='+$dataID+']').find('.serve-window .serve').addClass('hide');	
		setWindow(0, $dataID, $companyName);
		//set all window button disabled
	});	
	$('.modal .close').click(function(){
		$windowID = $('.modal .modal-btn').attr('data-window');
		$dataID = $('.modal .modal-btn').attr('data-id');
		$prevServe = $('.now-serve').find('.window-'+$windowID).attr('id');
		if($dataID != $prevServe)  $('.bin-row[data-id='+$dataID+']').find('.serve-window .w'+$windowID).removeClass('active');
	});
	$('.modal-btn').click(function(e){
		e.stopPropagation();	
		$dataID = $(e.currentTarget).attr('data-id');
		$windowID = $(e.currentTarget).attr('data-window');
		$('.serve-window .window#'+$dataID).siblings().removeClass("selected");
		$('.serve-window .window#'+$dataID).addClass("selected");
			$companyName = $('#comment-tbl').find('tr[data-id='+$dataID+']').find('td.company').html();		
		$('#comment-tbl').find('tr[data-id='+$dataID+']').find(".serve").removeClass('hide');	
		setWindow($windowID, $dataID, $companyName);
	});
}
function setWindow($windowID, $dataID, $companyName){	
	/*CHECK IF DATA ID ALREADY SET THEN REMOVE*/
	$prevServe = $('.now-serve').find('p#'+$dataID);
	$prevServe.html(' --- ');$prevServe.attr('id', '');
	$.ajax({
		type: 'POST',
		url: 'rwindow/set_window',  
		data: { transid: $dataID, wid: $windowID},
		success: function(data) {
			if(data.success == true){
				$nowserve = $('.now-serve').find('.window-'+$windowID);
				$nowserve.html($companyName);
				$nowserve.attr('id', $dataID);
			}
			PopulateItemsTable();
			$('.modal .close').click();
		},
		dataType: 'json'
	});
}
