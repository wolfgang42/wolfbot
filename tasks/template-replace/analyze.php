<?php
require_once(dirname(__FILE__).'/../../includes/SerialStoreArray.php');
require(dirname(__FILE__)."/control.php");
$checkedPages=new SerialStoreArray('template-replace',$replaceInfo['name'].'.checkedPages',array());
$users=array();
$dates=array();
echo "Pages using template {{[[".$replaceInfo['template']."]]}}:\n";
foreach ($checkedPages->getData() as $page=>$revinfo) {
	if (isset($users[$revinfo[0]])) {
		$users[$revinfo[0]]++;
	} else {
		$users[$revinfo[0]]=1;
	}
	$dates[]=$revinfo[1];
	echo "* [[:$page]]\n";
}

function ucmp($one,$two) {
	$a = $one[1];
	$b = $two[1];
	if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}

asort($users);
var_dump($users);

$data=$checkedPages->getData();
uasort($data,"ucmp");
var_dump($data);
