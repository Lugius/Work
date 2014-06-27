(function($) {

$.fn.barcode = function()
{
	this.change($.fn.barcode.changed).trigger('change');
}

$.fn.barcode.changed=function(e)
{
	$(this).val($(this).val().replace(/[^\d\w\$\/\+\%\*\.\-\_\\ ]/g,''));
	$('#'+$(this).attr('name')+'_barcodedisplay').attr('src','barcode.php?codetype=Code39&text='+$(this).val());
}

})(jQuery);
