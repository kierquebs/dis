(function () {	
datePick();
setDatatbl('#comment-tbl');
searchBox();
})();

function reset_pass(){
	$('.reset').click(function(e){
		$dataID = $(e.currentTarget).attr('data-id');	
		$.ajax({
			type: 'POST',
			url: 'admin/resetpass',  
			data: { id: $dataID},
			success: function(data) {
				alert(data.msg);
			},
			dataType: 'json'
		});
	})
}
function searchBox(){
	$('#search-form button[type=submit]').click(function(){
		$searchTxt = '';
		$search = $('#search-form input[name="search"]').val();
			if($search != '') $searchTxt = '&search='+$search;
		$type = $('#search-form select[name="type"]').val();
			if($type != '' && $type != undefined) $searchTxt += '&type='+$type;
		$status = $('#search-form select[name="status"]').val();
			if($status != '' && $status != undefined) $searchTxt += '&status='+$status;
		
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
	url: "admin/filter/get_mngt?page="+page+search,
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
				result.push(''); //USERNAME
				result.push(''); //EMAIL
				result.push(''); //UTYPE
				result.push(''); //STATUS	
				result.push(''); //ACCESS
				result.push(''); //BUTTON			

			$uTXT = $('<span></span>')
					.addClass('form-span')
					.text(value.user_name);		
			$emailTXT = $('<span></span>')
					.addClass('form-span')
					.text(value.email);						
			$statTXT = $('<span></span>')
					.addClass('form-span')
					.text((value.status == 1 ? 'ACTIVE' : 'INACTIVE'));						
			$editBTN = $('<a></a>')
					.addClass('btn btn-info glyphicon glyphicon-pencil')
					.attr({'href': 'admin/edit/'+value.user_id});	
					
			$resetBTN = $('<button></button>')
					.addClass('reset btn btn-info')
					.attr({'data-id': value.user_id})
					.text('reset pass');
			var newRow = myTable.row.add(result).draw().node(); 
				$(newRow).find('td:nth-child(2)').attr({'width':'200','data-name':'order'}).append($uTXT).end() 
				.find('td:nth-child(3)').append($emailTXT).end()
				.find('td:nth-child(4)').attr({'width':'100'}).append($statTXT).end()
				.find('td:nth-child(5)').addClass('td-action').attr({'width':'200'}).append($editBTN).append($resetBTN);
			});				
			pagination(data.total, data.per_page, false);
			reset_pass();
		}
		$.LoadingOverlay("hide");
	}
	});
}

