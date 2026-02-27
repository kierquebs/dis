(function () {	
	datePick();
	setDatatbl('#comment-tbl');
	modalSOA();
	$('#search-form button[type=submit]').click(function(){
		$searchTxt = '';
		$search = $('#search-form input[name="search"]').val();
			if($search != '') $searchTxt = '&search='+$search;
		$stat = $('#search-form select[name="status"]').val();
			if($stat != '' && $stat != undefined) $searchTxt += '&stat='+$stat;	
		$datef = $('#search-form input[name="datef"]').val();
			if($datef != '') $searchTxt += '&datef='+$datef;	
		$datet = $('#search-form input[name="datet"]').val();
			if($datet != '') $searchTxt += '&datet='+$datet;	
		actionFilter($searchTxt);
	});
})();
/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "POST",
	url: "resoa/get_soa?page="+page+search,
	dataType: "json",
	success: function (data) {
		myTable.rows().remove().draw();		
		$('#'+tblID+'_paginate, .export').remove();		
		if(data.totalData != 0){
			$jsonReturn = data.result;
				if(data.offset > 0) $i = data.offset + 1;
			$.each(data.result, function(key, value) {
				var result = [];
					result.push(value.order_id); 
					result.push(value.company_name); 
					result.push(value.date_return);
					result.push(value.date_received);
					result.push('');
						
					/*btn setup */
						
					 if( (value.btn_access == 5 && value.received == 0) || (value.btn_access == 3 && value.returned != 0 && value.received == 0)){						
						$btn = $('<input />')
							.addClass('check-class')
							.attr({'value':value.resoa_id, 'type':'checkbox','name':'check[]'});
							if((value.btn_access == 5 && value.returned != 0) || (value.btn_access == 3 && value.received != 0)) $($btn).attr('checked', true);
					 }else $btn = $('<span></span>').text(value.status);	
					
					var newRow = myTable.row.add(result).draw().node(); 
					$(newRow).find('td:nth-child(1)').attr({'width':150}).end()
							.find('td:nth-child(3)').attr({'width':200}).end()
							.find('td:nth-child(4)').attr({'width':200}).end()
							.find('td:nth-child(5)').addClass('text-center').attr({'width':100}).append($btn);
			});				
			pagination(data.totalData, data.per_page);
		}
	}
	});
}
function modalSOA(){
	var modalID = 'resoaModal';
	setModal(modalID, 'RETURNED SOA', 'YES');
	$('.btn-resoa').attr({'data-toggle':'modal' , 'data-target':'#'+modalID});	
	$('.modal .modal-btn').after('<button type="button" class="cancel btn btn-danger" data-dismiss="modal">CANCEL</button>');

	$('.btn-resoa').click(function(e){
		e.preventDefault();
		$allChecked = $('input[name="check[]"]:checked').length;
		if($allChecked == 0){
			$('.modal .cancel').hide();
			$('.modal .modal-btn').html('OK').addClass('btn-danger').removeClass('resoa-form');
			$('.modal .modal-body').html("YOU DID NOT CHECK ANY ORDER!");
		}else{
			$('.modal .cancel').show();
			$('.modal .modal-btn').html('YES').addClass('resoa-form').removeClass('btn-danger');
			$('.modal .modal-body').html("ARE YOU SURE?");
		}
		modalBTN();
	});	
	$('#checkAll').change(function (e) {
		e.preventDefault();
		$('input.check-class').prop('checked', $(this).prop('checked'));
	});
}
function modalBTN(){
	$('.btn-danger').click(function(){
		$('.modal .close').click()
	});
	$('.resoa-form').click(function(e){
		e.preventDefault();
		$('#resoa-form').submit();
	});
}
function genReport() {
	url = BASEURL + 'resoa/export?' + search;
	$('.export').attr('href', url).click();
}
