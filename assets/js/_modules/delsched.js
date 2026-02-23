var modalID = 'orderModal';
var btnaccess = (currenTime.getHours() >= timeStart && currenTime.getHours() <= timesUp.getHours() ? true : false);
var trbtn = false;
(function () {	
	datePick();
	datePick($('.delpicker'), true);
	setDatatbl('#queue-tbl');
	delBTN();
	setForm();
	timeCheck();
	acctManagerBox();
})();

function acctManagerBox(){
	$('.auto-manager').autocomplete({
		serviceUrl: 'delsched/manager',
		onSelect: function (suggestion) {
			$('.auto-manager').attr('data-id', suggestion.data);
		}
	});
	$('.auto-manager').attr({'autocomplete':'off'});
}

function timeCheck() {
	$(".ds-time #clock").countdowntimer({
		hours : timeStart,
		minutes : currenTime.getMinutes() ,
		seconds : currenTime.getSeconds(),
		//startDate : currenTime, // Set a start date.
		dateAndTime : timesUp,
		size : "lg",
		timeUp : timeIsUp,
		beforeExpiryTimeFunction : timeWarning  
	}); 
}
function timeWarning() {
	console.log('almost times up FASTER');
	$('.ds-time #clock').addClass('rwarning');
}
function timeIsUp() {
	btnaccess = false;
	console.log('times up');
	$('.ds-time #clock').removeClass('rwarning');
}

function diff_hours(timeNOW){	
	var dt1 = new Date(); 
		//if(dt1.getMonth() >= 9 && dt1.getMonth() <= 12) dt1.setHours(15, 30); 
		//else dt1.setHours(15);
	dt1.setHours(15);
	var diff = (timeNOW.getTime() - dt1.getTime()) / 1000; 
	diff /= (60 * 60);
  return Math.abs(Math.round(diff));
}

function delBTN(){
	$('.cancel-upload, .cancel-add').click(function(){
		$('.add-form , .upload-form').slideUp('slow');
		$('.error').remove();
		console.log('hey');
	})
	$('.upload-btndiv .show-upload').click(function(){
		$('.add-form').slideUp('slow');
		$('.upload-form').slideDown('slow');
		$('.error').remove();
	})		
	$('.upload-btndiv .show-add').click(function(){
		$('.add-form').slideDown('slow');
		$('.upload-form').slideUp('slow');
		$('.error').remove();
	})	
	$('#search-form button[type=submit]').click(function(){
		$searchTxt = '';
		$search = $('#search-form input[name="search"]').val();
			if($search != '') $searchTxt = '&search='+$search;
		$datef = $('#search-form input[name="datef"]').val();
			if($datef != '') $searchTxt += '&datef='+$datef;	
		$delmode = $('#search-form select[name="delmode"]').val();
			if($delmode != '' && $delmode != null) $searchTxt += '&delmode='+$delmode;	
		$delstatus = $('#search-form select[name="delstatus"]').val();
			if($delstatus != '' && $delstatus != null) $searchTxt += '&delstatus='+$delstatus;	
		$delpterm = $('#search-form select[name="delpterm"]').val();
			if($delpterm != '' && $delpterm != null) $searchTxt += '&delpterm='+$delpterm;	
		actionFilter($searchTxt);
	});
}


/*EDIT FORM SETUP*/
function setForm(){	
	setModal(modalID, 'DELIVERY SCHEDULE', 'SAVE CHANGES');
	$('.modal .modal-btn').attr('id', 'del-save').after('<button type="button" class="modal-cancel btn btn-danger" data-dismiss="modal">CANCEL</button>');

	$formDiv = $('<form></form>').addClass('row-div edit-div').attr({'method': 'get', 'action': 'javascript:', 'id':'sched_form'}); 
		$schedid = $('<input />').attr({'type': 'hidden', 'name': 'schedid', 'id':'schedid'});
		
		$cnameDIV = $('<div></div>').addClass('form-div');
			$($cnameDIV).append($('<div></div>').addClass('form-label text-label').text('COMPANY NAME:'));
			$($cnameDIV).append( $('<input />').addClass('auto-company required form-control').attr({'type': 'text', 'name': 'company_name'}) );		
		$orderDIV = $('<div></div>').addClass('form-div form-half');
			$($orderDIV).append($('<div></div>').addClass('text-label').text('ORDER ID:'));
			$($orderDIV).append( $('<input />').addClass('order-input required form-control').attr({'type': 'text', 'name': 'orderid'}) );	
		$amDIV = $('<div></div>').addClass('form-div');
			$($amDIV).append($('<div></div>').addClass('auto-manager form-label text-label').text('ACCOUNT MANAGER:'));
			$($amDIV).append( $('<input />').addClass('required form-control').attr({'type': 'text', 'name': 'am_name'}) );			
		$amountDIV = $('<div></div>').addClass('form-div form-half');
			$($amountDIV).append($('<div></div>').addClass('text-label').text('AMOUNT:'));
			$($amountDIV).append( $('<input />').addClass('order-input required form-control').attr({'type': 'text', 'name': 'amount'}) );	
		
		/* DELIVERY DETAILS */
		$delInsDIV = $('<div></div>').addClass('form-div');
			$($delInsDIV).append($('<div></div>').addClass('form-label text-label').css({width:'86px'}).text('DELIVERY INSTRUCTION:'));
			$($delInsDIV).append( $('<textarea></textarea>').addClass('required form-control').attr({'name': 'delinst'}).css({width: '80%', resize: 'none', height: '115px'}) );	
		$delModeDIV = $('<div></div>').addClass('form-div form-half');
			$($delModeDIV).append($('<div></div>').addClass('text-label').text('MODE OF DELIVERY:'));	
			$($delModeDIV).append( $('<select></select>').addClass('dropCat required form-control').attr({'name': 'delmode', 'id': 'form-category'}) );
		$dateDIV = $('<div></div>').addClass('form-div form-half');
			$($dateDIV).append($('<div></div>').addClass('text-label').text('DELIVERY DATE:'));
			$($dateDIV).append( $('<input />').addClass('delpicker required form-control').attr({'type': 'text', 'name': 'del_date', 'placeholder':'DATE'}) );
		
		/* PAYMENT TERMS */	
		$pModeDIV = $('<div></div>').addClass('form-div');
			$($pModeDIV).append($('<div></div>').addClass('form-label text-label').text('MODE OF PAYMENT:'));
			$($pModeDIV).append( $('<select></select>').addClass('required form-control delsched').attr({'name': 'pmode', 'id': 'form-pmode'}) );
		$pTermDIV = $('<div></div>').addClass('form-div');
			$($pTermDIV).append($('<div></div>').addClass('form-label text-label').text('PAYMENT TERMS:'));	
			$($pTermDIV).append( $('<select></select>').addClass('required form-control delsched').attr({'name': 'pterm', 'id': 'form-pterm'}) );
		$pNoteDIV = $('<div></div>').addClass('form-div'); 
			$($pNoteDIV).append($('<div></div>').addClass('form-label text-label').text('TREASURY REMARKS:'));
				$textArea = $('<textarea></textarea>').addClass('form-remarks required form-control').attr({'maxlength': '250', 'name': 'pnote', 'placeholder':'NOTES!'});
					if(trbtn == false) $textArea = $textArea.attr('readonly', 'readonly');
			$($pNoteDIV).append($textArea);
		
		$statDIV = $('<div></div>').addClass('form-div');
			$($statDIV).append($('<div></div>').addClass('form-label text-label').text('STATUS:'));
			$($statDIV).append( $('<select></select>').addClass('dropStat required form-control delsched').attr({'name': 'status', 'id': 'form-status'}) );

	$formError = $('<div></div>').addClass('error');
	$($formDiv).append($formError).append($schedid).append($cnameDIV).append($amDIV).append($orderDIV).append($amountDIV)
	.append($('<hr></hr>')).append($delModeDIV).append($dateDIV).append($delInsDIV).append($('<hr></hr>'))
	.append($pModeDIV).append($pTermDIV).append($statDIV).append($pNoteDIV)
	;	
	$('.modal .modal-body#'+modalID).html($formDiv);		
}

/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "GET",
	url: "delsched/get_del?page="+page+search,
	dataType: "json",
	error: function() {
		PopulateItemsTable();
	},
	success: function (data) {
		myTable.rows().remove().draw();
		$('#'+tblID+'_paginate, .export').remove();
		if(data.total != 0){
			$jsonReturn = data.result;
			if(data.offset > 0) $i = data.offset + 1;
			$.each(data.result, function(key, value) {
				$TEMPID = value.delsched_id;
				var result = [];
					for (x = 1; x <= 13; x++) { 
						result.push('');
					}
				if(data.btn_access == true && btnaccess == true){
					$btnEdit = $('<button></button>').addClass('sched-edit btn btn-sm btn-info glyphicon glyphicon-pencil').attr({'data-id': $TEMPID, 'data-toggle':'modal' , 'data-target':'#'+modalID});
				}else $btnEdit = '';

				$cname = $('<span></span>')
							.addClass('cname')
							.attr({'data-toggle':'tooltip', 'title':value.cname})
							.text(value.cname);

				$delinstruc = $('<span></span>')
							.addClass('str_elip')
							.attr({'data-toggle':'tooltip', 'title':value.del_instruc})
							.text(value.del_instruc);
							
				$date1 = $('<span></span>').addClass('datep form-span').text(value.date_created);	
				$date2 = $('<span></span>').addClass('dater form-span').text(value.user);				
				var newRow = myTable.row.add(result).draw().node(); 
				$(newRow).addClass('sched-tr').attr('data-id', $TEMPID) 
					.find('td:nth-child(1)').addClass('orderid').text(value.orderid).end()
					.find('td:nth-child(2)').addClass('company').append($cname).end()
					.find('td:nth-child(3)').addClass('am_name').text(value.am_name).end()
					.find('td:nth-child(4)').addClass('amount').text(value.amount).end()
					.find('td:nth-child(5)').addClass('del_instruc').append($delinstruc).end()
					.find('td:nth-child(6)').addClass('del_date').text(value.del_date).end()
					.find('td:nth-child(7)').addClass('del_mode '+value.mode_color).attr({'data-id': value.del_modeid}).text(value.del_mode).end()
					.find('td:nth-child(8)').addClass('p_term').text(value.p_term).end()
					.find('td:nth-child(9)').addClass('p_mode').text(value.p_mode).end()
					.find('td:nth-child(10)').addClass('statname').attr({'data-id': value.stat_id}).text(value.stat_name).end()
					.find('td:nth-child(11)').addClass('remarks').text(value.remarks).end()
					.find('td:nth-child(12)').addClass('queue-th-span').append($date1).append($date2).end()
					.find('td:nth-child(13)').addClass('queue-tbl-span').append($btnEdit);
			});	
			trbtn = data.treas_btn; 			
			pagination(data.total, data.per_page, true, 'LAST UPDATE');
			delschedBTN();
		}
	}
	});
}

function delschedBTN(){
	$('.sched-edit').click(function(e){		
		$(".modal .modal-cancel").remove();
		$('.modal .modal-body#'+modalID).html('');	
		$me = $(e.currentTarget);
		$formRow = $me.closest('.sched-tr');	
			$dataID = $me.attr('data-id');
		setForm();			
		$('.modal .modal-btn').show();
		$('form#sched_form input[name="schedid"]').val($dataID);
		$('form#sched_form input[name="orderid"]').val($formRow.find('td[data-name="order"] span').text());
		$('form#sched_form input[name="company_name"]').val($formRow.find('td.company').text());
		$('form#sched_form input[name="orderid"]').val($formRow.find('td.orderid').text());
		$('form#sched_form input[name="am_name"]').val($formRow.find('td.am_name').text());
		$('form#sched_form input[name="amount"]').val($formRow.find('td.amount').text());
		$('form#sched_form textarea[name="delinst"]').val($formRow.find('td.del_instruc').text());
		$('form#sched_form input[name="del_date"]').val($formRow.find('td.del_date').text());
		$('form#sched_form textarea[name="pnote"]').html($formRow.find('td.remarks').text());
	
		
		dropCat($("#form-category"),  0 , false, $formRow.find('td.del_mode').attr('data-id'), 'delsched');
		dropStatus($("#form-status"),  0 , true, $formRow.find('td.statname').attr('data-id'), 'delsched');
		dropCat($("#form-pterm"),  0 , false, $formRow.find('td.p_term').text(), 'delsched', 'get_pterm');
		dropCat($("#form-pmode"),  0 , false, $formRow.find('td.p_mode').text(), 'delsched', 'get_pmode');
		datePick($('.delpicker'), true);		
		acctManagerBox(); suggestionBox();
	})	
	$('#orderModal .close').click(function(e){
		$me = $(e.currentTarget);
		$('.modal .modal-title#'+modalID, '.modal .modal-body#'+modalID).html(''); $(".modal .modal-cancel").remove();
		
	});	
	
	$('.modal-cancel').click(function(e){
		$('.modal .close').click();
		$('.modal .modal-body').html("");
		$(".modal .modal-cancel").remove();
		PopulateItemsTable();
	});
	
	$('.modal-btn').click(function(e){
		$me = $(e.currentTarget);	
		$('.edit-div').find('span.error').remove;		
		update(e, modalID);
	});
}

function genReport() {
	url = BASEURL + 'delsched/export?' + search;
	$('.export').attr('href', url).click();
}

function update(e, modalID){
	$formName = $('form#sched_form');
		$dataID = $formName.find('input[name="schedid"]').val();
		$statID = $formName.find('select[name="status"]').val();
		$statOther = $formName.find('input[name="other"]').val();

	if($dataID == '') $formName.append('<span class="error">INVALID REQUEST DATA!</span>');
	else if($statID == '' || ($statID == '999' && $statOther == '')) $formName.append('<span class="error">INVALID STATUS!</span>');
	else{		
        e.stopImmediatePropagation();
		$.ajax({
			type: 'POST',
			url: 'delsched/update',  
			data: $formName.serialize(),	
			error: function() {
				//failureCallback();
			},
			success: function(data) {
				$('.modal #'+modalID+' .edit-div').find('.error').remove();
				if(data.success == true){	
					$('.modal .modal-btn').hide(); $(".modal .modal-cancel").text('OK');
					$('.modal .modal-body').html(data.msg);	
					PopulateItemsTable(); delschedBTN();
				}else $('.modal .error').text(data.msg);		
			},
			dataType: 'json'
		});
	}
}


