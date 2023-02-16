jQuery(document).ready(function(){
	jQuery('<a href="#" id="trigger">&nbsp;</a>').insertAfter('#rhwct_hide_duplicate_sku');
	jQuery('#progress').insertAfter('#rhwct_hide_duplicate_sku');
	jQuery('#finished').insertAfter('#rhwct_hide_duplicate_sku');
});

var bar = new ProgressBar.Line('#progress', {
  strokeWidth: 4,
  easing: 'easeInOut',
  duration: 1400,
  color: '#008000',
  trailColor: '#eee',
  trailWidth: 1,
  svgStyle: {width: '100%', height: '100%'},
});

jQuery(document).ready(function(){
	jQuery('#rhwct_hide_duplicate_sku').click(function(){
		jQuery('#finished').hide();
		jQuery('#progress').show();
		jQuery.ajax({
		'url': ajaxurl+'?action=hide_duplicate_sku_process&security='+rhwct_translation.ajax_nonce, 
		'success': function(d){
			if(d.post_count == 100){
				jQuery('#trigger').attr({'data-count':d.paged});
				jQuery('#trigger').attr({'data-time':d.time});
				jQuery('#trigger').trigger('click');
				bar.animate(d.time); 				
			 } else {
				bar.animate('1');
				setTimeout(function(){ 
					jQuery('#progress').hide(); 								
					jQuery('#finished').show();
				}, 1400);
			 }
			}
		});		
		return false;
	});
	
	jQuery('#trigger').click(function(){
		 jQuery.ajax({
			'url': ajaxurl+'?action=hide_duplicate_sku_process&security='+rhwct_translation.ajax_nonce+'&paged='+jQuery(this).attr('data-count')+'&time='+jQuery(this).attr('data-time'), 
			'success': function(d){
				if(d.post_count == 100){
					jQuery('#trigger').attr({'data-count':d.paged});
					jQuery('#trigger').attr({'data-time':d.time});
					jQuery('#trigger').trigger('click');
					bar.animate(d.time); 
				} else {
					bar.animate('1');
					setTimeout(function(){ 
						jQuery('#progress').hide(); 								
						jQuery('#finished').show();
					}, 1400);
				}
			}
		});
	});
});