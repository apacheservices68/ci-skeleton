<div id="footer">
<div class="container">
<div class="row footer-content">
<div class="col-md-4 footer-box">
<?php echo $about?>
</div>
<div class="col-md-4 footer-box">
<div class="footer-widget tweet_widget "><h4 class="widgettitle2">Visited</h4>
<div class="tweet">
<ul class="list-group">
<!-- 10 Bộ đếm -->
  <?php if(isset($counter_post)){?> 
  <li class="list-group-item">
    <a href="javascript:;"><span class="badge"><?php echo $counter_post;?></span></a>
    Viewer
  </li>
  <?php }?>
  <li class="list-group-item">
    <a href="#"><span class="badge badge-warning" id="user_online"><?php echo (isset($counter_online) && $counter_online>0)? $counter_online: 0;?></span></a>
    Online
  </li>                              
  <li class="list-group-item">
    <a href="#"><span class="badge"><?php echo (isset($counter_all) && $counter_all > 0) ? $counter_all : 0 ;?></span></a>
    Num of visited 
  </li>   
<!-- End bộ đếm -->
</ul>
</div>
</div>
</div>
<div class="col-md-4 footer-box">
<?php echo $contact;?>
            </div>
 
</div><!-- end footer - row -->
<div id="copyright">
<p><?php echo $copyright;?></p>
</div>

<div class="social">
<ul>
<li class="twitter"><a title="Twitter" href="#" target="_blank" rel="nofollow"></a></li>
<li class="facebook"><a title="Facebook" href="#" target="_blank" rel="nofollow"></a></li>
<li class="rss"><a title="RSS Feed" href="#" target="_blank" rel="nofollow"></a></li>
<li class="stumble"><a title="StumbleUpon" href="#" target="_blank" rel="nofollow"></a></li>
<li class="linkedin"><a title="Linkedin" href="#" target="_blank" rel="nofollow"></a></li>
<li class="vimeo"><a title="Vimeo" href="#" target="_blank" rel="nofollow"></a></li>
<li class="flickr"><a title="Flickr" href="#" target="_blank" rel="nofollow"></a></li>
<li class="picasa"><a title="Picasa" href="#" target="_blank" rel="nofollow"></a></li>        
</ul>
</div>
<div class="column-clear"></div>
</div><!--end container-->
</div><!--end footer-->