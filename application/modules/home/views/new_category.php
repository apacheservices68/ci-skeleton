<div class="wrap-content">
<div class="container">
<div class="row">
<?php echo $template['partials']['breadcrumb'];?>
</div>
<div class="row">
<div class="col-md-8 all-posts">
<?php echo ($items != '')? $items : '';?>
<?php echo (isset($is_post) && $is_post === true)? $template['partials']['detail_post']: "";?>
</div><!-- end all-posts -->
<div class="col-md-4 sidebar">
<ul>
<li  class="widget widget_text">
<?php echo (isset($search) && $search != '') ? $search: "";?>
</li>



<li id="text-2" class="widget widget_text">
<?php echo lang('make_reservation');?>
</li>

<li id="text-2" class="widget widget_text">

<ul class="list-group">
<!-- 10 Bộ đếm -->
  <?php if(isset($counter_post)){?> 
  <li class="list-group-item active">
    <span href="javascript:;"><?php echo $counter_post;?></span>
    Viewer
  </li>
  <?php }?>
  <li class="list-group-item ">
    <span id="user_online"><?php echo (isset($counter_online) && $counter_online>0)? $counter_online: 0;?></span>
    Online
  </li>                            

<!-- End bộ đếm -->
</ul>
</li>

<li id="ft_recent_post_image-2" class="widget ft_recent_post_image">
<h2 class="widgettitle"><?php echo lang('cf24_lang_latest_news');?></h2>
<div class="sidebar-articles">
<ul>
<?php echo ($top_3_news!='')? $top_3_news :"";?>        
</ul>
</div><!--sidebar-articles-->

</li><div class="column-clear"></div>

<li class="widget">
<h2 class="widgettitle">Facebook Like Box</h2>
<iframe src="http://www.facebook.com/plugins/likebox.php?href=https://www.facebook.com/cf24hKG.page&amp;width=292&amp;colorscheme=light&amp;show_faces=true&amp;connections=10&amp;stream=false&amp;header=false&amp;height=255" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:255px;" allowTransparency="true"></iframe>
</li>
<li class="widget ft_flickr_widget">

<h2 class="widgettitle">Flickr Widget</h2>			
<div class="flickr">
<script type="text/javascript" src="http://www.flickr.com/badge_code_v2.gne?count=10&amp;display=random&amp;size=s&amp;layout=x&amp;source=user&amp;user=37667275@N04"></script>
</div>
<div class="column-clear"></div>

</li><div class="column-clear"></div>

<li class="widget ft_video_embed">
<h2 class="widgettitle">Video Widget</h2>			
<div class="video-widget">
<iframe width="300" height="175" src="http://www.youtube.com/embed/badHUNl2HXU" frameborder="0" allowfullscreen></iframe>
<p>This is a embed video example.</p>
</div>

</li> 



</ul>    


</div><!-- end sidebar -->    

</div><!-- end row -->
</div><!-- end container -->
</div><!-- end wrapper-content-->