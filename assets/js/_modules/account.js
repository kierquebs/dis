(function () {
	editForm();
	accessForm();
})();
function editForm(){
	$('.btn-div .btn-edit').click(function(){
		$('.btn-div span').removeClass('hide');
		$('.prof-ul .form-div').removeClass('hide');
		$('.prof-ul .content').addClass('hide');
		$('.btn-div .btn-edit, .btn-div a').addClass('hide');
	});
	$('.btn-div span .btn-cancel').click(function(){
		$('.btn-div .btn-edit, .btn-div a').removeClass('hide');
		$('.prof-ul .content').removeClass('hide');
		$('.prof-ul .form-div').addClass('hide');
		$('.btn-div span').addClass('hide');
		$('.error').remove();
	});
}
function accessForm(){
	$('.catSelect').change(function(){
        $(".utype-form").submit();
	})
}
