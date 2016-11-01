<?php
session_start();

include_once "vendor/autoload.php";

$query_url = 'https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp';

$schno = "";
$teaid = "";

$url_param = "";
$url = "";
if (isset($_POST["schoolid"])) {
	$schno = $_POST["schoolid"];
	$schno = str_replace(' ', ',', $schno);
	$schno = str_replace('/', ',', $schno);

    if( mb_strpos( $schno, '國中') > 0 or mb_strpos( $schno, '國小') >0 or mb_strpos( $schno, '國中小') >0 ){
        $school_info = get_school_info_by_name($schno);
        header( 'location:index.php?schoolid=' . $school_info[ 0 ]->id );
    }

	if (count($ar_schno = explode(',', $schno)) == 1 and substr( $schno, 0, 3) != '014') {
        if( isChi( $ar_schno[0])){
            $seltype = 'tea';
            $tea_name = $ar_schno[0];
            $schno = $_SESSION[ 'schoolid' ];
            $teachers = getData(file_get_contents($query_url . "?schno=$schno"), "teachers");
            foreach ($teachers as $teacher) {
                foreach ($teacher as $key => $value) {
                    if ($tea_name == $value) {
                        $teaid = $key;
                        break;
                    }
                }
            }
            $url_param = sprintf('schno=%s&seltype=%s&teaid=%s', $schno, $seltype, $teaid);


        }else{
            $seltype = 'cls';
            $schno = $_SESSION[ 'schoolid' ];
            $clsno = $ar_schno[0];
            $year = substr($clsno, 0, 1);
            $url_param = sprintf("schno=%s&seltype=cls&clsno=%s&year=%s", $schno, $clsno, $year);
        }

    }elseif( count($ar_schno = explode(',', $schno)) == 2){
		$schno = $ar_schno[0];
		if (empty($schno)) {
			$schno = $_SESSION["schoolid"];
		}

		if (isChi($ar_schno[1])) {
			$seltype = 'tea';
			$tea_name = $ar_schno[1];

			$teachers = getData(file_get_contents($query_url . "?schno=$schno"), "teachers");
			foreach ($teachers as $teacher) {
				foreach ($teacher as $key => $value) {
					if ($tea_name == $value) {
						$teaid = $key;
						break;
					}
				}
			}
			$url_param = sprintf('schno=%s&seltype=%s&teaid=%s', $schno, $seltype, $teaid);
//            print $url_param . '<br />';
		} else {
			$seltype = 'cls';
			$clsno = $ar_schno[1];
			$year = substr($clsno, 0, 1);
			$url_param = sprintf("schno=%s&seltype=cls&clsno=%s&year=%s", $schno, $clsno, $year);
		}

	} else {
		$url_param = sprintf("schno=%s", $schno);
	}


	$url = sprintf("https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?%s", $url_param);
} elseif (isset($_GET["schoolid"]) and isset($_GET["teaid"])) {
	$schno = $_GET["schoolid"];
	$teaid = $_GET["teaid"];
	$url = sprintf("https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?schno=%s&seltype=tea&teaid=%s", $schno, $teaid);
} elseif (isset($_GET["schoolid"]) and isset($_GET['clsno'])) {
	$schno = $_GET["schoolid"];
	$year = substr($_GET["clsno"], 0, 1);
	$clsno = $_GET["clsno"];
	$url = sprintf("https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?schno=%s&seltype=cls&year=%s&clsno=%s", $schno,
		$year, $clsno);
}elseif( isset($_GET["schoolid"]) ){
    $schno = $_GET['schoolid'];
    $url = sprintf("https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?schno=%s", $schno);
}

$_SESSION["schoolid"] = $schno;


?>
<!Doctype html>
<html lang="zh-hant">
<head>
    <meta charset="UTF-8">
    <title>取回教師列表</title>
    <style>
        table{
            margin: 0.4em auto;
            border-collapse: collapse;
        }
        tr{
            height: 1.8em;
        }
        td{
            width: 100px;
            hieght: 100px;
            border: 1px solid gray;
            text-align: center;
        }
        tr:first-child{
            background-color: #ddd;
        }
        td:first-child{
            background-color: #eee;
        }

        a {
            text-decoration: none;
        }
        a:link, a:visited{
            color: blue;
        }
        a:hover{
            background-color:blue;
            color:white;
        }

        .queryForm {
            margin: 0 auto;
            text-align: center;
            padding: 1em 5em;
            width: 450px;
            /*padding-top: 1em;*/
            /*padding-bottom: 1em;*/
            background-color: powderblue;
        }
        .queryForm input[type=text]{
            padding-left: 0.3em;
            padding-right: 0.3em;
            width: 250px;
        }
        .header p{
            text-align: right;
            margin:0;
            padding:0;
            font-size: 10pt;
        }
        .header a {
            text-decoration: none;

        }
        .container{
            width:550px;
            margin: 0 auto;
        }

        .information{
            background-color: #DFF2BF;
            margin: .3em 0;
            padding: .3em 0;

        }
        .information:hover {
            background-color: darkgreen;
            color: #DFF2BF;
        }
        .footer{
            margin: 0  auto;

        }
    </style>
</head>
<body>
    <div class="queryForm">
        <form method="POST">
            學校代碼：<input id="schoolid" name="schoolid" type="text" placeholder="目前學校名稱或代碼：<?=$_SESSION["schoolid"]?>" autofocus />
        </form>
    </div>
</body>
</html>
<?php

if (!isset($url) or empty($url)) {
	die();
}

$class_table = file_get_contents($url);
$class_table = strip_tags($class_table, '<table><tr><td></td></tr></table><select><option></option></select>');


if (!isset($teachers)) {
	$teachers = getData($class_table, "teachers");
}

$classes = getData($class_table, "classes");

$classtable_name = getClassTableName($class_table);

//showClassTable($class_table);
print array2table(getTable($class_table, $teachers), $classtable_name);

// end
?>
<div class="container footer">
    <a href="changes.php" title="修訂記事">修訂記事(last modified: 2016/10/31)</a>
</div>

<?php
// functions

function dump($obj) {
	echo "<pre>";
	var_dump($obj);
	echo "</pre>";
}

function getClassTableName($content) {

	preg_match_all('/([一-龜0-9 ]+)　課表/is', $content, $matches);
	return $matches[1][0];
}

function getData($content, $return_type = "classes") {

	$ret = '';

	$dom = new PHPHtmlParser\Dom();
	$dom->load($content);
//    echo $dom->find('.lesson_word');

//    preg_match_all('/([一-龜 ]+)　課表/is', $content, $matches);
	//    $classtable_name = $matches[1][0]

	if ($return_type === "classes") {
		// 各年級的班級數
		preg_match_all('/var (txtid[0-9]{1,2}) *= *"([^"]*)"/is', $content, $matches);
		foreach ($matches[2] as $value) {
			if (empty($value)) {
				continue;
			}

			$classes = explode(",", $value);
			$grade[substr($value, 0, 1)] = count($classes);
			$ret[substr($value, 0, 1)] = array(substr($value, 0, 1) => $classes);
		}
	} elseif ($return_type === "teachers") {
		// 教師 id 與姓名

		$teachers_select_tag = $dom->find('#teaid', 0);

		$i = 0;
		while ($teacher = $teachers_select_tag->find("option", ++$i)) {
			$ret[] = array($teacher->getAttribute("value", 0) => $teacher->text);
//            $ret = array_push( $teacher->getAttribute("value", 0) => $teacher->text);
			//            $ret[$teacher->getAttribute("value", 0)] = $teacher->text;
		}
	}
	return $ret;
}

function showClassTable($content) {
	$dom = new PHPHtmlParser\Dom();
	$dom->load($content);
	echo $dom->find('.lesson_word');
}

function getTable($content, $teachers, $type = 0) {
	$dom = new PHPHtmlParser\Dom();
	$dom->load($content);
	$dom_class = $dom->find('.lesson_word');

//    echo $dom_class;

	$i = 0;
	while ($row = $dom_class->find('tr', $i++)) {
		$row_count = count($row->find("td"));

		if ($row_count <= 3) {
			if ($type === 1) {
				$table[0][$i - 1] = $row->find("td", 0)->text;
				for ($j = 2; $j <= 6; $j++) {
					$table[$j - 1][$i - 1] = "";
				}
			} else {
				$table[$i - 1][0] = $row->find("td", 0)->text;
				for ($j = 2; $j <= 6; $j++) {
					$table[$i - 1][$j - 1] = "";
				}
			}
		} else {
			$j = 0;
			while ($item = $row->find("td", $j++)) {
//                echo $item->text;
				if (withClassName($item->text)) {
					if ($type === 1) {
						$table[$j - 1][$i - 1] = convertDigit($item->text);
					} else {
						$table[$i - 1][$j - 1] = convertDigit($item->text);
					}

				} else {
					if ($type === 1) {
						$table[$j - 1][$i - 1] = $item->text;
					} else {
						$table[$i - 1][$j - 1] = $item->text;
					}

				}
			}
		}
	}

	// 加上科任老師課表
	for ($i = 0; $i < count($table); $i++) {
		for ($j = 0; $j < count($table[$i]); $j++) {
//            if( empty(table[$i][$j]) ) continue;
			foreach ($teachers as $teacher) {
				foreach ($teacher as $teaid => $tea_name) {

					if (mb_strpos($table[$i][$j], $tea_name) !== false) {

						$table[$i][$j] = str_replace($tea_name, sprintf('<a href=index.php?schoolid=%s&seltype=tea&teaid=%s>%s</a>', $_SESSION["schoolid"], $teaid, $tea_name), $table[$i][$j]);

					}
				}
			}
		}
	}

	return $table;
}

function withTeacherName($str) {
	return preg_match('/《([一-龜]+)》/is', $str);
}

function withClassName($str) {
	return preg_match('/《(([一-龜]+)年([一-龜0-9]+)班)》/is', $str);
}

function convertDigit($str) {
	preg_match_all('/《(([一-龜0-9]+)年([一-龜0-9]+)班)》/is', $str, $matches);
	$classname_chi = $matches[1][0];
	$classname_digit = getChineseDigit($matches[2][0]) . zero(getChineseDigit($matches[3][0]), 2);

	$classname_digit = sprintf('<a href="index.php?schoolid=%s&seltype=cls&year=%s&clsno=%s">%s</a>', $_SESSION['schoolid'], getChineseDigit($matches[2][0]), $classname_digit, $classname_digit);
	$classname = str_replace($classname_chi, $classname_digit, $str);

	return $classname;
}

function getChineseDigit($s, $prefix = "") {
	$ret = "";

	$numstringalpha = '0123456789';
	$numstringchinese1 = '○一二三四五六七八九十';
	$numstringchinese2 = '○忠孝仁愛信義和平';
	$numstringchinese3 = '○甲乙丙丁戊己庚辛壬癸';
	if (preg_match("/$s/is", $numstringalpha)) {
        $numstringchinese = $numstringalpha;
    }elseif (preg_match("/$s/is", $numstringchinese2)) {
		$numstringchinese = $numstringchinese2;
	} elseif (preg_match("/$s/is", $numstringchinese3)) {
		$numstringchinese = $numstringchinese3;
	} else {
		$numstringchinese = $numstringchinese1;
	}

	if ((mb_strlen($s) == 1) and ($s === '十')) {
		$ret .= '10';
	} else {

		for ($i = 0; $i < mb_strlen($s); $i++) {
//        print mb_substr($s, $i, 1) . "<br />";
			$c = mb_substr($s, $i, 1);
			if ($c === '十') {
				$ret .= '1';
			}
			$ret .= mb_substr($numstringalpha, mb_strpos($numstringchinese, $c), 1);
		}
	}

	if (mb_strlen($ret) < 2 and mb_strlen($ret) > 0) {
		$ret = $prefix . $ret;
	}
	return $ret;
}

function zero($str, $len = 2) {
	$zerostring = '00000000000000000000000000000000000000000';
	if (mb_strlen($str) < $len) {
		$str = mb_substr($zerostring, 0, $len - mb_strlen($str)) . $str;
	}
	return $str;
}

/**
 * Translate a result array into a HTML table
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.3.2
 * @link        http://aidanlister.com/2004/04/converting-arrays-to-human-readable-tables/
 * @param       array  $array      The result (numericaly keyed, associative inner) array.
 * @param       bool   $recursive  Recursively generate tables for multi-dimensional arrays
 * @param       string $null       String to output for blank cells
 */
function array2table($array, $caption, $recursive = false, $null = '&nbsp;') {
	// Sanity check
	if (empty($array) || !is_array($array)) {
		return false;
	}

	if (!isset($array[0]) || !is_array($array[0])) {
		$array = array($array);
	}

	// Start the table
	$table = "<table>\n";

//    // The Caption
	$table .= "\t<caption>$caption";
	$table .= "</caption>\n";

//    // The header
	//    $table .= "\t<tr>";
	//    // Take the keys from the first row as the headings
	//    foreach (array_keys($array[0]) as $heading) {
	//        $table .= '<th>' . $heading . '</th>';
	//    }
	//    $table .= "</tr>\n";

	// The body
	foreach ($array as $row) {
		$table .= "\t<tr>";
		foreach ($row as $cell) {
			$table .= '<td>';

			// Cast objects
			if (is_object($cell)) {$cell = (array) $cell;}

			if ($recursive === true && is_array($cell) && !empty($cell)) {
				// Recursive mode
				$table .= "\n" . array2table($cell, true, true) . "\n";
			} else {
				$table .= (strlen($cell) > 0) ?
//                    htmlspecialchars((string) $cell) :
				(string) $cell :
				$null;
			}

			$table .= '</td>';
		}

		$table .= "</tr>\n";
	}

	$table .= '</table>';
	return $table;
}

/**
 * 判斷是否有中文
 *
 * @param $str
 * @return int
 */
function isChi($str) {
	return preg_match('/[一-龜]/is', $str);
}

/**
 * echo 函數變化版
 * @param $s
 */
function echobr($s) {
	print "$s <br />";
}



/**
 * 由學校代號取得學校資訊
 *
 * @param string $id
 * @param string $return_type
 * @return mixed|null
 */
function get_school_info_by_id($id='', $return_type = 'json'){
    if( $id == "" ) return null;

    $query_url = 'http://data.ntpc.gov.tw/od/data/api/365714DB-5840-46E6-AD3F-CD5BF6460D20?$format=json';

    $query_url .= '&$filter=' . rawurlencode(sprintf( 'id eq %s', $id));

    $JSON = json_decode(file_get_contents( $query_url ));

    return $JSON;

}

/**
 * 由學校名稱取得學校資訊
 *
 * @param string $name
 * @param string $return_type
 * @return mixed|null
 */
function get_school_info_by_name($name='', $return_type = 'json'){
    if( $name == "" ) return null;

    $query_url = 'http://data.ntpc.gov.tw/od/data/api/365714DB-5840-46E6-AD3F-CD5BF6460D20?$format=json';

    $query_url .= '&$filter=' . rawurlencode(sprintf( 'alias eq %s', $name));

    $JSON = json_decode(file_get_contents( $query_url ));

    return $JSON;

}