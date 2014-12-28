<?php
#****************************************#
# * @Author: lehieu008                   #
# * @Email: lehieu008@gmail.com          #
# * @Website: http://www.iscvietnam.net  #
# * @Copyright: 2008 - 2009              #
#****************************************#
if(!defined('BASEPATH'))exit('No direct script access allowed');
/**
 *Class Bbcode:
 *  function light($subject): Chuyen ma BBCode ve HTML
**/
class Bbcode
{
    #Array of bbcode patterns
	protected $patterns = array
	(
		'/\[b\](.+)\[\/b\]/Uis',
		'/\[i\](.+)\[\/i\]/Uis',
		'/\[u\](.+)\[\/u\]/Uis',
		'/\[left\](.+)\[\/left\]/Uis',
		'/\[center\](.+)\[\/center\]/Uis',
		'/\[right\](.+)\[\/right\]/Uis',
		'/\[justify\](.+)\[\/justify\]/Uis',
		'/\[blockquote\](.+)\[\/blockquote\]/Uis',
		'/\[hr\]/Uis',
		'/\[fieldset\](.+)\[\/fieldset\]/Uis',
		'/\[legend\](.+)\[\/legend\]/Uis',
		'/\[sup\]/Uis',
		'/\[ul\](.+)\[\/ul\]/Uis',
		'/\[s\](.+)\[\/s\]/Uis',
		'/\[url=(.+)\](.+)\[\/url\]/Ui',
		//'/\[img\](.+)\[\/img\]/Ui',
		'/\[code\](.+)\[\/code\]/Uis',
		'/\[green\](.+)\[\/green\]/Ui',
		'/\[blue\](.+)\[\/blue\]/Ui',
		'/\[red\](.+)\[\/red\]/Ui',
		'/\[xx-small\](.+)\[\/xx-small\]/Ui',
		'/\[x-small\](.+)\[\/x-small\]/Ui',
		'/\[small\](.+)\[\/small\]/Ui',
		'/\[medium\](.+)\[\/medium\]/Ui',
		'/\[large\](.+)\[\/large\]/Ui',
		'/\[x-large\](.+)\[\/x-large\]/Ui',
		'/\[xx-large\](.+)\[\/xx-large\]/Ui',
		'/\[arial\](.+)\[\/arial\]/Ui',
		'/\[time\](.+)\[\/time\]/Ui',
		'/\[tahoma\](.+)\[\/tahoma\]/Ui',
		'/\[:\)]/Uis',
		'/\[:-\)]/Uis',
		'/\[\(:]/Uis',
		'/\[\(-:]/Uis',
		'/\[(:)]/Uis',
		'/\[(-:)]/Uis',
		'/\[(:-)]/Uis'
	);

	#Array of HTML tags that correspond to bbcode patterns
	protected $replacements = array
	(
		'<b>\1</b>',
		'<i>\1</i>',
		'<u>\1</u>',
		'<div style="text-align:left;">\1</div>',
		'<div style="text-align:center;">\1</div>',
		'<div style="text-align:right;">\1</div>',
		'<div style="text-align:justify;">\1</div>',
		'<blockquote>\1</blockquote>',
		'<hr>',
		'<fieldset style="padding:5px; margin-top:5px; margin-bottom:5px; border:1px #CCCCCC solid;">\1</fieldset>',
		'<legend style="color:#06F;">\1</legend>',
		'&sup2;',
		'<ul><li>\1</li></ul>',
		'<s>\1</s>',
		'<a href = "\1" target = "_blank" rel="nofollow">\2</a>',
		//'<img src = "\1" alt = "Image" />',
		'<pre>\1</pre>',
		'<span style = "color: #0F0;">\1</span>',
		'<span style = "color: #03F;">\1</span>',
		'<span style = "color: #F00;">\1</span>',
		'<font size="1">\1</font>',
		'<font size="2">\1</font>',
		'<font size="3">\1</font>',
		'<font size="4">\1</font>',
		'<font size="5">\1</font>',
		'<font size="6">\1</font>',
		'<font size="7">\1</font>',
		'<font face="Arial">\1</font>',
		'<font face="Times New Roman">\1</font>',
		'<font face="Tahoma">\1</font>',
		'<img src="http://e360.vn/templates/editor/images/yahoo_bigsmile.gif" alt="Image" />',
		'<img src="http://e360.vn/templates/editor/images/yahoo_batting.gif" alt="Image" />',
		'<img src="http://e360.vn/templates/editor/images/yahoo_kiss.gif" alt="Image" />',
		'<img src="http://e360.vn/templates/editor/images/yahoo_question.gif" alt="Image" />',
		'<img src="http://e360.vn/templates/editor/images/yahoo_sad.gif" alt="Image" />',
		'<img src="http://e360.vn/templates/editor/images/yahoo_smiley.gif" alt="Image" />',
		'<img src="http://e360.vn/templates/editor/images/yahoo_wink.gif" alt="Image" />'
	);

	function light($subject)
	{
		$subject = nl2br($subject);
		$subject = preg_replace($this->patterns, $this->replacements, $subject);
		return $subject;
	}
}