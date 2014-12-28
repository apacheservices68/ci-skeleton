<div class="wrap-content">
<div class="container">
<div class="row">
<?php echo $template['partials']['breadcrumb'];?>
</div>
	<ul class="portfolio-categ">
  	<?php echo ($second_child_menu != '') ? $second_child_menu : '';?>
    <div class="column-clear"></div>
    </ul>
<?php echo $items;?>
   

</div><!-- end container -->
</div><!-- end wrapper-content-->