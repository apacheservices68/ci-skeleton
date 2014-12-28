<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="description" content="" />
<meta name="keyword" content="" />
<meta name="author" content="" />
<title>Title</title>    
<link href="<?php echo base_url('public/private/css/bootstrap.min.css');?>" rel="stylesheet"/>
<link href="<?php echo base_url('public/private/css/custom.css');?>" rel="stylesheet"/>    
<link href="<?php echo base_url('public/private/css/plugins/metisMenu/metisMenu.min.css');?>" rel="stylesheet"/>    
<link href="<?php echo base_url('public/private/css/plugins/timeline.css');?>" rel="stylesheet"/>    
<link href="<?php echo base_url('public/private/css/sb-admin-2.css');?>" rel="stylesheet"/>    
<link href="<?php echo base_url('public/private/css/plugins/morris.css');?>" rel="stylesheet"/>
<link href="<?php echo base_url('public/private/plugins/icheck/skins/all.css');?>" rel="stylesheet"/>    
<link href="<?php echo base_url('public/private/font-awesome-4.1.0/css/font-awesome.min.css');?>" rel="stylesheet" type="text/css">    
<link href="<?php echo base_url('public/private/css/plugins/datatables/bootstrap.css');?>" rel="stylesheet"/>
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<script src="<?php echo base_url('public/private/js/jquery.min.js');?>"></script>
</head>
<body>
<div id="wrapper">
<script>
var base_url     = '<?php echo site_url();?>';
var token        = '<?php echo $this->security->get_csrf_hash(); ?>';
var ext          = '<?php echo $this->config->item('url_suffix')?>';
</script>
<?php echo $template['partials']['headers'];?>
<?php echo $template['body'];?>
</div>
</body>
<script src="<?php echo base_url('public/private/js/bootstrap.min.js');?>"></script>
<script src="<?php echo base_url('public/private/js/plugins/dataTables/jquery.dataTables.js');?>"></script>
<script src="<?php echo base_url('public/private/js/plugins/dataTables/dataTables.bootstrap.js');?>"></script>
<script src="<?php echo base_url('public/private/js/plugins/dataTables/reload.js');?>"></script>
<script src="<?php echo base_url('public/private/js/plugins/autocomplete/jquery.min.js');?>"></script>
<script src="<?php echo base_url('public/private/plugins/icheck/icheck.min.js');?>"></script>
<script src="<?php echo base_url('public/private/js/plugins/form/jquery.min.js');?>"></script>
<script src="<?php echo base_url('public/private/js/plugins/validate/jquery.min.js');?>"></script>
<script src="<?php echo base_url('public/private/js/plugins/validate/add.jquery.min.js');?>"></script>
<script src="<?php echo base_url('public/private/js/custom.js');?>"></script>
<script src="<?php echo base_url('public/private/js/plugins/metisMenu/metisMenu.min.js');?>"></script>
<script src="<?php echo base_url('public/private/js/sb-admin-2.js');?>"></script>
</html>
