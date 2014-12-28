<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php echo (isset($meta) && $meta != '') ? $meta : '';?>       
<title><?php echo $template['title'];?></title>
<?php echo (isset($cf24_css) && $cf24_css != '') ? $cf24_css : '';?>
<script src="<?php echo base_url('assets/template/js/jquery.min.js')?>" type="text/javascript"></script>
</head>	
<body>
<script>
var base_url     = "<?php echo site_url();?>";
var suf          = "<?php echo $url_suffix;?>";
var token        = "<?php echo $this->security->get_csrf_hash(); ?>";
var token_name   = "<?php echo $this->config->item('csrf_cookie_name');?>";
</script>
<?php echo (isset($js_base_facebook) && $js_base_facebook!='')? $js_base_facebook: '';?>
<div class="group_lang">
<?php echo $lang_content;?>
</div>
<a style="display: none;" href="#<?php echo ($this->session->userdata('cm_anchor') && $this->session->userdata('cm_anchor')!= '')? $this->session->userdata('cm_anchor') : '';?>" class="_scroll">C</a>
    <?php echo $template['partials']['header'];?>
    <?php echo $template['partials']['menu'];?>
    <?php echo (isset($template['partials']['slide']) && $template['partials']['slide'] != '') ? $template['partials']['slide'] : '';?>    
    <?php if($is_index_admin){?>
    <?php echo $template['partials']['explode'];?>
    <?php }?>
    <?php echo $template['body'];?>
    <?php echo $template['partials']['footer'];?>
<p id="backTop"><a href="#top">Top&uarr;</a></p>
<?php echo (isset($cf24_js) && $cf24_js != '') ? $cf24_js : '';?>
<script type="text/javascript">
$(document).ready(function() {
$(".lightbox").fancybox();
});
</script>    
<?php echo (isset($custom_javascript) && $custom_javascript!='') ? $custom_javascript : '';?>
<?php echo (isset($analytic_js) && $analytic_js!='') ? $analytic_js : '';?>
</body>
</html>