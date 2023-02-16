/* 
 * Сustomizer Script
 * @package rehub
 */
 
 jQuery(document).ready(function($) {
	'use strict';

	ShowHideFunc(
	   $('#_customize-input-rehub_sticky_nav-radio-0'),
	   $('#_customize-input-rehub_sticky_nav-radio-1'),
	   $('#customize-control-rehub_logo_sticky_url')
	);
   
	var menuconditionals = {
		"header_seven" : ["header_seven_more_element", "header_seven_wishlist_label", "header_seven_wishlist", "header_seven_login_label", "header_seven_login", "header_seven_cart_as_btn", "header_seven_cart", "header_seven_compare_btn_label", "header_seven_compare_btn"],
		"header_six" : ["header_six_menu", "header_six_src", "header_six_btn_login", "header_six_btn_url", "header_six_btn_txt", "header_six_btn_color", "header_six_btn", "header_six_login"],
		"header_five" : ["header_six_src", "header_six_btn_login", "header_six_btn_url", "header_six_btn_txt", "header_six_btn_color", "header_six_btn", "header_six_login"],
	};

	var commonitems = [];
	$.each(menuconditionals, function(index, value){
		commonitems = commonitems.concat(value);
	});
	var commonitemsunique = new Set(commonitems);
	commonitems = Array.from(commonitemsunique); //Create array without duplicates

	var selectedheader = $('#_customize-input-rehub_header_style').val(); //Get current value of header style
	ShowHideHeaderElements(menuconditionals[selectedheader], commonitems); //Show items on loading
	$('#_customize-input-rehub_header_style').on('change', function(){
		var selectedValue = $(this).val();
		ShowHideHeaderElements(menuconditionals[selectedValue], commonitems); //Show items on change
	});	

	function ShowHideHeaderElements(showarray, fullarray){
		$.each(fullarray, function(index, value){
			if($.inArray(value, showarray) !== -1){
				$('#customize-control-'+value).fadeIn();
			}else{
				$('#customize-control-'+value).fadeOut();
			}
		});
	}
   
	function ShowHideFunc(button0,button1,container){
		if(button1.is(":checked")){
			container.show();
		}else{
			container.hide();
		}
		button1.click(function(){
			container.fadeIn();
		});
		button0.click(function(){
			container.fadeOut();
		});
	}
   
}); //END Document.ready