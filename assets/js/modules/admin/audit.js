(function () {	
datePick();
setDatatbl();
searchBox();
})();
function searchBox(){
	$('#search-form button[type=submit]').click(function(){
		$searchTxt = '';
		$search = $('#search-form input[name="search"]').val();
			if($search != '') $searchTxt = '&search='+$search;
		$type = $('#search-form select[name="type"]').val();
			if($type != '' && $type != undefined) $searchTxt += '&type='+$type;
		$stat = $('#search-form select[name="stat"]').val();
			if($stat != '' && $stat != undefined) $searchTxt += '&stat='+$stat;
		$mod = $('#search-form select[name="mod"]').val();
			if($mod != '' && $mod != undefined) $searchTxt += '&mod='+$mod;
		$datef = $('#search-form input[name="datef"]').val();
			if($datef != '') $searchTxt += '&datef='+$datef;
		$datet = $('#search-form input[name="datet"]').val();
			if($datet != '') $searchTxt += '&datet='+$datet;
		
		if($searchTxt != ''){
			search = $searchTxt;
			PopulateItemsTable();
		}
	});
}
/*
* AJAX POPULATE
*/
function PopulateItemsTable() {
	$.ajax({
	type: "GET",
	url: "admin/filter/get_audit?page="+page+search,
	dataType: "json",
	beforeSend: $.LoadingOverlay("show"),
	success: function (data) {
		myTable.rows().remove().draw();
		$('#'+tblID+'_paginate, .export').remove();
		if(data.total != 0){
			$jsonReturn = data.result;
			$i = 1;
			if(data.offset > 0) $i = data.offset + 1;
			$.each($jsonReturn, function(key, value) {						
			var result = [];
				result.push($i++); //num
				result.push(''); //userinfo
				result.push(''); //module
				result.push(''); //details
				result.push(value.date); //datetime				

			$uTXT = $('<span></span>')
					.addClass('form-span')
					.text(value.uname);		
			$emailTXT = $('<span></span>') 
					.addClass('form-span')
					.text(value.uemail);		
			$utypeTXT = $('<span></span>')
					.addClass('form-span')
					.text(value.utype);	
			$moduleTXT = (value.module != '' ? $('<span></span>')
					.addClass('form-span')
					.text(value.module) : '' );	
			$actionTXT = $('<span></span>')
					.addClass('form-span')
					.text(value.action);	
			$orderTXT = (value.oid != 0 ? $('<span></span>')
					.addClass('form-span')
					.text( value.oid) : '');	
			$otherTXT = $('<span></span>')
					.addClass('form-span')
					.text(value.other);	
					
			var newRow = myTable.row.add(result).draw().node(); 
				$(newRow).addClass('queue-tr')
				.find('td:nth-child(2)').addClass('queue-th-span').append($uTXT).append($emailTXT).append($utypeTXT).end() 
				.find('td:nth-child(3)').addClass('queue-th-span').append($moduleTXT).append($actionTXT).end()
				.find('td:nth-child(4)').addClass('queue-th-span').append($orderTXT).append($otherTXT);
			});				
			pagination(data.total, data.per_page, true, 'DATETIME');
		}
		$.LoadingOverlay("hide");
	}
	});
}
function genReport() {
	url = BASEURL + 'admin/filter/export_au?' + search;
	$('.export').attr('href', url).click();
}
