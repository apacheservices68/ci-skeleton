<?php ob_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>SB Admin - Bootstrap Admin Template</title>
    <!-- Bootstrap Core CSS -->
    <link href="<?php echo base_url('assets/backend/css/bootstrap.min.css');?>" rel="stylesheet" type='text/css' />
    <link href="<?php echo base_url('assets/backend/css/fuelux.min.css');?>" rel="stylesheet" type='text/css' />
    <!-- Custom CSS -->
    <link href="<?php echo base_url('assets/backend/css/sb-admin-2.css');?>" rel="stylesheet" type='text/css' />
    <link href="<?php echo base_url('assets/backend/css/custom.css');?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/backend/css/bootstrap-select.min.css');?>" rel="stylesheet" type="text/css" />
    <!-- Custom Fonts -->
    <link href="<?php echo base_url('assets/backend/font-awesome-4.1.0/css/font-awesome.min.css');?>" rel="stylesheet" type="text/css" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libackend/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libackend/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php if(isset($is_index_admin) && $is_index_admin == false){?>
    <?php $css_files = $output->css_files;?>
    <?php if($css_files != null){?>
    <?php foreach($css_files as $file): ?>
    <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
    <?php endforeach; ?>
    <?php }?>
    <?php }?>
    <script src="<?php echo base_url('assets/backend/js/jquery-1.11.0.js');?>"></script>    
</head>
<body class="fuelux">
<div id="wrapper">
<script>
var base_url     = '<?php echo site_url();?>';
var token        = '<?php echo $this->security->get_csrf_hash(); ?>';
var ext          = '<?php echo $this->config->item('url_suffix')?>';
</script>
<?php echo $template['partials']['headers'];?>
<?php echo $template['body'];?>
</div>
<!-- jQuery Version 1.11.0 -->

<!-- Bootstrap Core JavaScript -->
<!-- child of the body tag -->
<span id="top-link-block" class="hidden">
    <a href="#top" class="btn btn-primary" onclick="$('html,body').animate({scrollTop:0},'slow');return false;">
        <i class="glyphicon glyphicon-chevron-up"></i>
    </a>
</span><!-- /top-link-block -->
<script src="<?php echo base_url('assets/backend/js/bootstrap-select.js');?>"></script>
<script src="<?php echo base_url('assets/backend/js/bootstrap.min.js');?>"></script>
<script src="<?php echo base_url('assets/backend/js/plugins/metisMenu/metisMenu.min.js');?>"></script>
<script src="<?php echo base_url('assets/backend/js/sb-admin-2.js');?>"></script>
<script src="<?php echo base_url('assets/backend/js/custom.js');?>"></script>
<script src="<?php echo base_url('assets/backend/js/fuelux.min.js');?>"></script>
<script src="<?php echo base_url('assets/editor/ckeditor/ckeditor.js');?>"></script>
<?php if(!isset($is_index_admin) && $is_index_admin == false){?>
<?php $js_files = $output->js_files;?>
<?php if($js_files != null){?>
<?php foreach($js_files as $file): ?>
<script src="<?php echo $file; ?>" type="text/javascript"></script>
<?php endforeach; ?>
<?php }?>
<?php }?>
</body>
</html>