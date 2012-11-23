<?php
/** requestedmoves.php - A fork of requestedmoves.php for improving on said script
 *  TRIAL Version 4.02
 *
 *  (c) 2010 James Hare - http://en.wikipedia.org/wiki/User:Harej
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *   
 *  Developers (add your self here if you worked on the code):
 *    James Hare - [[User:Harej]] - Wrote everything
 *    WBM - [[User:Wbm1058]] - August 2012 updates
 *    Wolfgang - [[User:Wolfgang42]] - Integrated with WolfBot
 **/

const ds = 86400;    #number of seconds in a day
const ditmax = 8;    #array of dates, then backlog

require_once(dirname(__FILE__)."/../../includes/task.php");
class RequestedMoves extends Task {
	function __toString() {
		return "requestedmoves";
	}
	function run(WolfBot $wolfbot) {
		$wolfbot->checkShutoff('requestedmoves');
		file_put_contents('fakeedit.txt','');
		$objwiki=$wolfbot->getWiki('en.wikipedia.org');
		
		$d = array(date("F d, Y"), date("F d, Y", time()-ds), date("F d, Y", time()-ds*2), date("F d, Y", time()-ds*3),
		 date("F d, Y", time()-ds*4), date("F d, Y", time()-ds*5), date("F d, Y", time()-ds*6), date("F d, Y", time()-ds*7));
		print_r($d);
		 
		echo "Checking for transclusions...";
		$transcludes = $objwiki->getTransclusions("Template:Requested move/dated");
		print_r($transcludes);
		 
		$addition = "";
		 
		for ($i = 0; $i < count($transcludes); $i++) {
				echo "\n__________\n" . $i . " Retrieving $transcludes[$i] contents...\n";
		 		
		 		# TODO this appears to be some sort of error correction mechanism... clean it up!
		 		$contents="";
				$breakcounter = 0;
				while ($contents == "") {
				        if ($breakcounter == 5) {
				                break;
				        } else {
				                $contents = $objwiki->getpage($transcludes[$i]);
				                $breakcounter += 1;
				        }
				}
				$breakcounter = 0;
				#echo "contents:\n";
				#echo "$contents";
				#echo "\n";
				
				# Section
				preg_match("/=+\s?.*\s?=+(?=\n+.*\{{2}(Requested move\/dated|movereq|rename)+[^}]*\}{2}+)/iu", $contents, $m);
				$section[$transcludes[$i]] = preg_replace("/=+\s*/", "", $m[0]);
				$section[$transcludes[$i]] = preg_replace("/\s*=+\n*/", "", $section[$transcludes[$i]]);
				#echo "Section: " . $section[$transcludes[$i]] . "\n";
				# remove links from section titles
				$section[$transcludes[$i]] = preg_replace("/\[\[/", "", $section[$transcludes[$i]]);
				$section[$transcludes[$i]] = preg_replace("/\]\]/", "", $section[$transcludes[$i]]);
				echo "Section> " . $section[$transcludes[$i]] . "\n";
				if ($section[$transcludes[$i]] == "") {
				        echo "It's NULL!!\n";
				}
		 
				# Newtitle
				$regexpart1 = "/\{{2}\s?(Requested move|movereq|move|rename)\s?[^}]*";
				$regexpart2 = "\}{2}/iu";
		 		
		 		# TODO why is this loop needed?
		 		$nt[0]="";
				while ($nt[0] == "") {
				        preg_match($regexpart1 . $regexpart2, $contents, $nt);
				        $regexpart1 .= "\n";
				        $breakcounter += 1;
				        if ($breakcounter == 5) {
				                echo "Breaking from regex loop!!\n";
				                break;
				        }
				}
		 
				#echo "nt ";
				#print_r($nt);
				$breakcounter = 0;
				$newtitlemeta = preg_replace("/\n+/", "", $nt[0]);
				$newtitlemeta = preg_replace("/ ?\| ?/", "|", $newtitlemeta);
				$newtitlemeta = preg_replace("/\{{2}\s?/", "", $newtitlemeta);
				$newtitlemeta = preg_replace("/\s?}{2}/", "", $newtitlemeta);
				$components = explode("|", $newtitlemeta);
				#echo "components ";
				#print_r($components);

				for ($multi = 1; $multi < count($components); $multi++) {
				        #echo "multi " . $multi . "-->" . $components[$multi] . "\n";
				        if (preg_match("/^current\d+\s?=\s?/i", $components[$multi], $check)) {
				                preg_match("/\d+/", $check[0], $number);
				                $number = $number[0] - 1;
				                $currentname[$transcludes[$i]][$number] = preg_replace("/^current\d+\s?=\s?/i", "", $components[$multi]);
				                echo "Current name> " . $number . ": " . $currentname[$transcludes[$i]][$number] . "\n";
				                continue;
				        }
				        elseif (preg_match("/^new\d+\s?=\s?/i", $components[$multi], $check)) {
				                preg_match("/\d+/", $check[0], $number);
				                $number = $number[0] - 1;
				                $newname[$transcludes[$i]][$number] = preg_replace("/\s?new\d+\s?=\s?/i", "", $components[$multi]);
				                echo "New name> " . $number . ": " . $newname[$transcludes[$i]][$number] . "\n";
				                if ($newname[$transcludes[$i]][$number] == "") {
				                        $newname[$transcludes[$i]][$number] = "?";
				                        echo "\nSetting NULL newname to ?";
				                }
				                continue;
				        }
				}
				#if ($newname[$transcludes[$i]][0] == "") {
				if (!isset($newname[$transcludes[$i]][0]) || $newname[$transcludes[$i]][0] == "") {
				                $currentname[$transcludes[$i]][0] = preg_replace("/(\s|_)?talk/i", "", $transcludes[$i]);
				                $newname[$transcludes[$i]][0] = str_replace("1=", "", $components[1]);
				}
				for ($nom = 0; $nom < count($currentname[$transcludes[$i]]); $nom++) {
				        echo "Current name: " . $nom . ": " . $currentname[$transcludes[$i]][$nom] . "\n";
		 
				        if (preg_match("/^(User|Wikipedia|File|MediaWiki|Template|Help|Category|Portal):/i",$currentname[$transcludes[$i]][$nom],$tpcp)) {
				                $talkname = str_replace($tpcp[1],$tpcp[1].' talk',$currentname[$transcludes[$i]][$nom]);
				        }
				        else {
				                $talkname = "Talk:" . $currentname[$transcludes[$i]][$nom];
				        }
		 
				        if ($nom != 0) {
				                $break = 0;
				                $talkpage = "";
				                while ($talkpage == "") {
				                        if ($break == 5) {
				                                break;
				                        }
				                        else {
				                                $talkpage = $objwiki->getpage($talkname);
				                                $break += 1;
				                        }
				                }
				                $check = strpos($talkpage, "<!-- " . $transcludes[$i] . " crosspost -->");
				                if ($check === false && $objwiki->nobots($talkname,BOT_USERNAME,$talkpage) == true) {
				                        $talkpage .= "\n\n==Move discussion in progress==\nThere is a move discussion in progress on [[" . $transcludes[$i] . "#" . $section[$transcludes[$i]] .
				                         "|" . $transcludes[$i] . "]] which affects this page. Please participate on that page and not in this talk page section. Thank you. <!-- " .
				                         $transcludes[$i] . " crosspost --> —[[User:RMCD bot|RMCD bot]] ~~~~~";
				                        $objwiki->edit($talkname,$talkpage,"Notifying of move discussion",false,true);
				                }
				                unset($talkpage);
				        }
				}
				for ($nom = 0; $nom < count($newname[$transcludes[$i]]); $nom++) {
				        echo "New name: " . $nom . ": " . $newname[$transcludes[$i]][$nom] . "\n";
				}
		 
				# Description and Timestamp
				$regex1 = "/\{{2}\s?(Requested move\/dated|movereq|rename)\s?[^}]*\}{2}";
				$regex2 = "([0-2]\d):([0-5]\d),\s(\d{1,2})\s(\w*)\s(\d{4})\s\(UTC\).*/i";

				for ($lim = 0; $lim < 50; $lim++) {
						$fellThrough=true;
				        $regex1 .= "\n*.*";
				        preg_match($regex1 . $regex2, $contents, $m);
				        if (!isset($m[0])) continue;
				        #echo "m ";
				        #var_dump($m);
				        $description[$transcludes[$i]] = preg_replace("/\{{2}\s?(Requested move\/dated|movereq|rename)\s?[^}]*\}{2}\n*/i", "", $m[0]);
				        $description[$transcludes[$i]] = preg_replace("/\n/", "", $description[$transcludes[$i]]);
				        #echo "$lim Description->" . $description[$transcludes[$i]] . "\n";

				        $description[$transcludes[$i]] = preg_replace("/\[{2}.*?\]{2}\s*?→\s*?(\{{2}|\[{2}|).*?(\}{2}|\]{2}|\?)\s*?/", "", $description[$transcludes[$i]]);
		 
				        if (preg_match("/Relisted/i", $description[$transcludes[$i]]) === 1) {
				                preg_match("/([0-2]\d):([0-5]\d),\s(\d{1,2})\s(\w*)\s(\d{4})\s\(UTC\)/i", $description[$transcludes[$i]], $t);
				        }
				        else {
				                preg_match("/([0-2]\d):([0-5]\d),\s(\d{1,2})\s(\w*)\s(\d{4})\s\(UTC\)/i", $description[$transcludes[$i]], $t, 0, strlen($description[$transcludes[$i]])-32);
				        }
				        #print_r($t);
				        $timestamp[$transcludes[$i]] = strtotime($t[0]);
				        if ($description[$transcludes[$i]] != "") {
				        		$fellThrough=false;
				                break;
				        }
				}
				if ($fellThrough) echo "WARN: fell through bottom of loop!\n";
				echo "Description: " . $description[$transcludes[$i]] . "\n";
				echo "Timestamp: " . $timestamp[$transcludes[$i]] . " - " . $t[0] . "\n";
		 
				# Checking for moveheader
		#       preg_match("/\{{2}\s?moveheader/i", $contents, $mhcheck);
		#       if ($mhcheck[0] == "") {
		#               $contents = "{{moveheader|section=" . $section[$transcludes[$i]] . "}}\n" . $contents;
		#               $objwiki->edit($transcludes[$i],$contents,"Adding moveheader",false,true);
		#       }
				
				unset($nt);
				unset($newtitlemeta);
				unset($components);
				unset($contents);
		}
		 
		echo "\n__________\nSorting by timestamp... ";
		$keys = array_keys($timestamp);
		$values = array_values($timestamp);
		array_multisort($values, SORT_DESC, $keys);
		$timestamp = array_combine($keys, $values);
		echo "done.\n";
		 
		 
		echo "Adding entries to different lists...\n";
		foreach ($timestamp as $title => $time) {
				#echo "\nDescription= " . $description[$title] . "\n";
				$description[$title] = preg_replace("/^((.*?)*\s?(&mdash;|—|&ndash;|–)\s?)/", "", $description[$title]);
				#echo "Description> " . $description[$title] . "\n";
				$description[$title] = preg_replace("/\*{1,2}\s?\[{2}[^\]]*\]{2}\s?→\s?\[{2}[^\]]*\]{2}/", "", $description[$title]);
		 
				if ($newname[$title][0] == "?") {
				        $theaddition = "* " . "''([[" . $title . "#" . $section[$title] . "|Discuss]])'' – '''[[" . $currentname[$title][0] . "]] → ?''' – " . $description[$title] . "\n";
				        $oldaddition = "* " . "'''[[" . $currentname[$title][0] . "]] → ?''' – (''[[" . $title . "#" . $section[$title] . "|Discuss]]'') – " . $description[$title] . "\n";
				        $summaddition = "*[[" . $currentname[$title][0] . "]] → ? – '''([[" . $title . "#" . $section[$title] . "|Discuss]])'''\n";
				}
				else {
				        $theaddition = "* " . "''([[" . $title . "#" . $section[$title] . "|Discuss]])'' – '''[[" . $currentname[$title][0] . "]] → {{no redirect|" . $newname[$title][0] . "}}''' – " .
				         $description[$title] . "\n";
		 
				        $oldaddition = "* " . "'''[[" . $currentname[$title][0] . "]] → {{no redirect|" . $newname[$title][0] . "}}''' – (''[[" . $title . "#" . $section[$title] . "|Discuss]]'') – " .
				         $description[$title] . "\n";
		 
				        $summaddition = "*[[" . $currentname[$title][0] . "]] → {{no redirect|" . $newname[$title][0] . "}} – '''([[" . $title . "#" . $section[$title] . "|Discuss]])'''\n";
				}
		 
				for ($indent = 1; $indent < count($currentname[$title]); $indent++) {
				        if ($newname[$title][$indent] == "?") {
				                $theaddition .= "** [[" . $currentname[$title][$indent] . "]]  → ?\n";
				                $oldaddition .= "** [[" . $currentname[$title][$indent] . "]]  → ?\n";
				        }
				        else {
				                $theaddition .= "** [[" . $currentname[$title][$indent] . "]]  → {{no redirect|" . $newname[$title][$indent] . "}}\n";
				                $oldaddition .= "** [[" . $currentname[$title][$indent] . "]]  → {{no redirect|" . $newname[$title][$indent] . "}}\n";
				        }
				}
		 
				$theaddition .= "\n";
				$oldaddition .= "\n";
		 
				for ($dit = 0; $dit < ditmax; $dit++) {
					if (!isset($add[$dit])) $add[$dit]="";
            		if (!isset($oldadd[$dit])) $oldadd[$dit]="";
            		if (!isset($summ[$dit])) $summ[$dit]="";
			        if ($time > 0) {
		                if (date("F d, Y", $time) == $d[$dit]) {
	                        $add[$dit] .= $theaddition;
	                        $oldadd[$dit] .= $oldaddition;
	                        $summ[$dit] .= $summaddition;
		                }
		                else {
		                    continue;
		                }
			        }
				}
		 		if (!isset($BLadd)) $BLadd="";
		        if (!isset($BLold)) $BLold="";
		        if (!isset($BLsumm)) $BLsumm="";
		       	if (!isset($MALadd)) $MALadd="";
		        if (!isset($MALold)) $MALold="";
		        if (!isset($MALsumm)) $MALsumm="";
				if ($time < strtotime($d[ditmax-1]) && $time != "") {
				        $BLadd .= $theaddition;
				        $BLold .= $oldaddition;
				        $BLsumm .= $summaddition;
				}
				elseif ($time == "") {
				        $MALadd .= $theaddition;
				        $MALold .= $oldaddition;
				        $MALsumm .= $summaddition;
				}
		}
		 
		$submission = "";
		$oldsubmission = "";
		$summsubmission = "";
		 
		for ($dit = 0; $dit < ditmax; $dit++) {
				$submission .= "===" . $d[$dit] . "===\n";
				$submission .= $add[$dit];
				$oldsubmission .= "===" . $d[$dit] . "===\n";
				$oldsubmission .= $oldadd[$dit];
				$summsubmission .= "{{User:Wbm1058/coordcollapsetop|c=#BDD8FF|'''[[Wikipedia:Requested moves#" . $d[$dit] . "|" . $d[$dit] . "]]}}\n";
				#$summsubmission .= "| group" . $counter . " = [[Wikipedia:Requested moves#" . $d[$dit] . "|" . $d[$dit] . "]]\n";
				$summsubmission .= $summ[$dit] . "\n{{collapse bottom}}\n";
		}
		 
		if ($BLadd != "") {
				$submission .= "===Backlog===\n";
				$submission .= $BLadd;
				$oldsubmission .= "===Backlog===\n";
				$oldsubmission .= $BLold;
				$summsubmission .= "{{User:Wbm1058/coordcollapsetop|c=#BDD8FF|'''[[Wikipedia:Requested moves#Backlog|Backlog]]'''}}\n";
				$summsubmission .= $BLsumm . "\n{{collapse bottom}}\n";
				$wprm = $objwiki->getpage("Wikipedia:Requested moves");
				$wprm = str_replace("{{adminbacklog|bot=RMCD bot|disabled=yes}}", "{{adminbacklog|bot=RMCD bot}}", $wprm);
				$objwiki->edit("Wikipedia:Requested moves",$wprm,"Adding backlog notice",false,true);
		}
		else {
				$wprm = $objwiki->getpage("Wikipedia:Requested moves");
				$wprm = str_replace("{{adminbacklog|bot=RMCD bot}}", "{{adminbacklog|bot=RMCD bot|disabled=yes}}", $wprm);
				$objwiki->edit("Wikipedia:Requested moves",$wprm,"Removing backlog notice",false,true);
		}
		 
		if ($MALadd != "") {
				$submission .= "===Time could not be ascertained===\n";
				$submission .= $MALadd;
				$oldsubmission .= "===Time could not be ascertained===\n";
				$oldsubmission .= $MALold;
				$summsubmission .= "{{User:Wbm1058/coordcollapsetop|c=#BDD8FF|'''[[Wikipedia:Requested moves#Time could not be ascertained|Time could not be ascertained]]'''}}\n";
				$summsubmission .= $MALsumm . "\n{{collapse bottom}}\n";
		}
		 
		echo "\nPosting the new requested pagemoves...\n";
		$objwiki->edit("Wikipedia:Requested moves/Current discussions",$submission,"Updating requested pagemoves list",false,true);
		$objwiki->edit("Wikipedia:Requested moves/Current discussions (alt)",$oldsubmission,"Updating requested pagemoves list",false,true);
		$objwiki->edit("Wikipedia:Dashboard/Requested moves",$summsubmission,"Updating requested pagemoves list",false,true);
		echo "done.\n";
		 
		echo "\nMission accomplished.\n\n";
	}
	private function fakeedit($page,$data,$summary = '',$minor = false,$bot = true,$section = null,$detectEC=false,$maxlag='') {
		file_put_contents("fakeedit.txt",
			file_get_contents("fakeedit.txt").
			"*** \"$page".($section==null?"":"#$section").'" '.($minor?"m":"").($bot?"b":"")." ($summary)\n$data\n\n\n");
	}
}
$wolfbot->runTask(new RequestedMoves());

