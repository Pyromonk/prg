<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json; charset=utf-8');
$d = json_decode(file_get_contents('php://input'), true);
/*$d = array('payload'=>array(
	array('drm'=>true, 'episodeCount'=>9, 'slug'=>'test/test1', 'image'=>array('showImage'=>'http://whatever.img'), 'title'=>'test1'),
	array('drm'=>true, 'episodeCount'=>0, 'slug'=>'test/test2', 'image'=>array('showImage'=>'http://whatever.img'), 'title'=>'test2'),
	array('drm'=>false, 'episodeCount'=>1, 'slug'=>'test/test3', 'image'=>array('showImage'=>'http://whatever.img'), 'title'=>'test3'),
	array('episodeCount'=>2, 'slug'=>'test/test4', 'image'=>array('showImage'=>'http://whatever.img'), 'title'=>'test4'),
	'skip'=>0, 'take'=>10, 'totalRecords'=>75
));
$d = json_encode($d);
$d = json_decode($d, true);*/
if($d) {
	$r = array();
	if(is_array($d['payload']) || is_object($d['payload'])) { foreach($d['payload'] as $v) {
		if($v['drm'] && $v['episodeCount']>0) {
			array_push($r, array('image'=>$v['image']['showImage'], 'slug'=>$v['slug'], 'title'=>$v['title']));
		}
	} }
	$r = array('response'=>$r);
} else {
	header('X-PHP-Response-Code: 400', true, 400);
	$r = array('error'=>'Could not decode request: JSON parsing failed');
}
echo stripcslashes(json_encode($r));
?>
