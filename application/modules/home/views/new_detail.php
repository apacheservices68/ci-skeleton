<h4 class="comm-title"><?php echo (isset($total_cmt) && $total_cmt!='') ? $total_cmt : "0";?> <?php echo lang('cf24_lang_response_post');?> “<?php echo (isset($post_title) && $post_title != '')? $post_title : "";?>”</h4>
<div class="tabs2">
<ul class="tabs">
<li><a href="#tab0"><?php echo lang('cf24_our_comment_title');?></a></li>
<li><a href="#tab1"><?php echo lang('cf24_facebook_comment_title');?></a></li>
<li><a href="#tab2"><?php echo lang('cf24_disqus_comment_title');?></a></li>
</ul>
<div class="tab_container">
<div id="tab0" class="tab_content">
    <?php echo (isset($comment_content) && $comment_content != '') ? $comment_content : '';?>
</div>
<div id="tab1" class="tab_content">
<div class="fb-comments" data-width="707" data-href="<?php echo current_url();?>" data-numposts="5" data-colorscheme="light"></div>
</div>
<div id="tab2" class="tab_content">
    <div id="disqus_thread"></div>
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = 'shophangdep'; // required: replace example with your forum shortname
        var disqus_identifier = '<?php echo $disqus_id;?>';
    var disqus_title = "<?php echo $template['title'];?>";
    var disqus_url = '<?php echo $disqus_url;?>';
        /* * * DON'T EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
  
</div>
</div>
</div>
	
    
 
<div id="respond">
	<h4 class="comm-title" >Leave a Comment </h4>
	<?php echo (isset($form) && $form != '') ? $form : '';?>
</div>