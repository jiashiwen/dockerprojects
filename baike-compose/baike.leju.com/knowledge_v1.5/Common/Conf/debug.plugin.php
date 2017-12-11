<?php
if ( IS_CLI ) {
	return false;
}
// 调试模式下，使用 forp 进行调试
$DEV_IGNORES = array('api');
if ( function_exists('forp_start') && APP_DEBUG===true && !in_array(APP_NAME, $DEV_IGNORES) ) {
	forp_start();
	register_shutdown_function(
		function() {
			if ( APP_DEBUG!==true || 
				( defined('AJAX_OUTPUT') && constant('AJAX_OUTPUT')===true )
			) {
				return true;
			}
			forp_end();
		?>
		<script src="//leju-knowledge.b0.upaiyun.com/c/j/forp.js"></script>
		<script>
		(function($) {
			$(".forp")
			 .each(
				function() {
					$(this).attr('style', 'margin:10px;height:300px;border:1px solid #333');
				}
			 )
			 .forp({
				stack : <?php echo json_encode(forp_dump()); ?>,
				//mode : "fixed"
			 })
		})(jMicro);
		</script>		
		<?php
		}	// end function definition
	);
}