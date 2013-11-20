<?php
require('HtmlAttributeFilter.class.php');

$str = '<div class="bd clearfix" id="index_hilite_ul"><ul class="list"><li><img src="http://su.bdimg.com/static/skin/img/logo_white.png" width="118" height="148"><div class="cover"><a class="text" href="http://www.csdn.net"><strong>yuna</strong><p>love</p></a><strong class="t g">want to know</strong><a href="/login.html" class="ppBtn"><strong class="text">YES</strong></a></div></li></ul></div>';

$obj = new HtmlAttributeFilter();

// 允许id属性
$obj->setAllow(array('id'));

$obj->setException(array(
                    'a' => array('href'),   // a 标签允许有 href属性特例
                    'ul' => array('class')  // ul 标签允许有 class属性特例
));

// img 标签忽略,不过滤任何属性
$obj->setIgnore(array('img'));

echo 'source str:<br>';
echo htmlspecialchars($str).'<br><br>';
echo 'filter str:<br>';
echo htmlspecialchars($obj->strip($str));
?>