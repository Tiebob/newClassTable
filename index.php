<?php
session_start();

include_once "vendor/autoload.php";

$query_url = 'https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp';

//echo $_POST["schoolid"] . "<br />";
if( isset( $_POST["schoolid"] ) ){
    $schno = $_POST["schoolid"];
    $schno = str_replace(' ', ',', $schno);
    $schno = str_replace('/', ',', $schno);

    if( count($ar_schno = explode(',', $schno)) > 1){
        $schno = $ar_schno[0];
        if( empty($schno) ) $schno = $_SESSION["schoolid"];

        if( isChi( $ar_schno[1]) ){
            $seltype = 'tea';
            $tea_name = $ar_schno[1];

            $teachers = getData(file_get_contents($query_url . "?schno=$schno"), "teachers");
            foreach( $teachers as $teacher ){
                foreach($teacher as $key => $value ) {
                    if ( $tea_name == $value ) {
                        $teaid = $key;
                        break;
                    }
                }
            }
            $url_param = sprintf('schno=%s&seltype=%s&teaid=%s', $schno, $seltype, $teaid);
//            print $url_param . '<br />';
        }else{
            $seltype = 'cls';
            $clsno = $ar_schno[1];
            $year = substr($clsno, 0, 1);
            $url_param = sprintf("schno=%s&seltype=cls&clsno=%s&year=%s", $schno, $clsno, $year);
        }

    }else{
        $url_param = sprintf("schno=%s", $schno);
    }
    $url = sprintf("https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?%s", $url_param);
}elseif( isset( $_GET["schoolid"]) and isset($_GET["teaid"]) ){
    $schno = $_GET["schoolid"];
    $teaid = $_GET["teaid"];
    $url = sprintf("https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?schno=%s&seltype=tea&teaid=%s", $schno, $teaid);
}elseif( isset( $_GET["schoolid"]) and isset($_GET['clsno']) ) {
    $schno = $_GET[ "schoolid" ];
    $year = substr( $_GET[ "clsno" ], 0, 1 );
    $clsno = $_GET[ "clsno" ];
    $url = sprintf( "https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?schno=%s&seltype=cls&year=%s&clsno=%s", $schno,
        $year, $clsno );
}
//    $class_table = file_get_contents( sprintf("https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?schno=%s&seltype=tea&teaid=%s", $_GET["schno"], $_GET["teaid"]) );

$_SESSION["schoolid"] = $schno;
//print $url . "<br />";

?>
<!Doctype html>
<html lang="zh-hant">
<head>
    <meta charset="UTF-8">
    <title>取回教師列表</title>
    <style>
        table{
	    width: 700px;
            margin: 0.4em auto;
            border-collapse: collapse;
        }
        tr{
            height: 1.8em;
        }
        td{
            width: 100px;
            height: 50px;
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

        .information {
	    margin: 0 auto;
	    text-align: center;
	    padding: 0.3em 5em;
	    width: 550px;
	    background-color: #DFF2BF;
	    height: 18px;
	}

	.queryForm {
            margin: 0 auto;
            text-align: center;
            padding: 1em 5em;
            width: 550px;
            /*padding-top: 1em;*/
            /*padding-bottom: 1em;*/
            background-color: powderblue;
        }
        .queryForm input[type=text]{
            padding-left: 0.3em;
            padding-right: 0.3em;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="information">2015/10/28: 修正班級會出現不正確的問題</div>
    <br />
    <div class="queryForm">
        <form method="POST">
            學校代碼：<input id="schoolid" name="schoolid" type="text" placeholder="目前學校代碼：<?=$_SESSION["schoolid"]?>" autofocus />
        </form>
    </div>
</body>
</html>
<?php
/**
 * Created by PhpStorm.
 * User: bob
 * Date: 2015/4/11
 * Time: 下午 01:50
 */

//if ( $_SERVER["REQUEST_METHOD"] !== "POST" )  die();
//include_once "vendor/autoload.php";
//use PHPHtmlParser\Dom;


if( !isset( $url )) die();

$class_table = file_get_contents( $url );
$class_table = strip_tags($class_table, '<table><tr><td></td></tr></table><select><option></option></select>');

//print $class_table;

if( !isset($teachers) ) $teachers = getData($class_table, "teachers");
$classes = getData($class_table, "classes");

$classtable_name = getClassTableName($class_table);

//showClassTable($class_table);
print array2table( getTable($class_table, $teachers) , $classtable_name);


//die();

function dump($obj){
    echo "<pre>";
    var_dump($obj);
    echo "</pre>";
}



function getClassTableName($content){

    preg_match_all('/([一-龜 ]+)　課表/is', $content, $matches);
    return  $matches[1][0];
}

function getData($content, $return_type="classes"){
    $ret = '';

    $dom = new PHPHtmlParser\Dom();
    $dom->load($content);


    if( $return_type === "classes"){
        // 各年級的班級數
        preg_match_all('/var (txtid[0-9]{1,2}) *= *"([^"]*)"/is', $content, $matches);
        foreach ($matches[2] as $value) {
            if( empty( $value )) continue;

            $classes = explode(",", $value);
            $grade[ substr($value, 0, 1) ] = count($classes);
            $ret[substr($value, 0, 1)] = array( substr($value, 0, 1) => $classes);
        }
    }elseif( $return_type === "teachers"){
        // 教師 id 與姓名
        $teachers_select_tag = $dom->find('#teaid', 0);
        $i=0;
        while( $teacher = $teachers_select_tag->find("option", ++$i) ){
            $ret[] = array( $teacher->getAttribute("value", 0) => $teacher->text);
//            $ret = array_push( $teacher->getAttribute("value", 0) => $teacher->text);
//            $ret[$teacher->getAttribute("value", 0)] = $teacher->text;
        }
    }
    return $ret;
}

function showClassTable($content){
    $dom = new PHPHtmlParser\Dom();
    $dom->load($content);
    echo $dom->find('.lesson_word');
}

function getTable($content, $teachers, $type=0){
    $dom = new PHPHtmlParser\Dom();
    $dom->load($content);
    $dom_class = $dom->find('.lesson_word');

//    echo $dom_class;

    $i=0;
    while( $row = $dom_class->find('tr', $i++)) {
        $row_count = count( $row->find( "td" ) );

        if ( $row_count <= 3 ) {
            if( $type === 1){
                $table[0][$i-1] = $row->find("td", 0)->text;
                for ( $j = 2; $j <= 6; $j++ ) {
                    $table[ $j-1 ][ $i-1 ] = "";
                }
            }else{
                $table[$i-1][0] = $row->find("td", 0)->text;
                for ( $j = 2; $j <= 6; $j++ ) {
                    $table[ $i-1 ][ $j-1 ] = "";
                }
            }
        } else {
            $j = 0;
            while ( $item = $row->find( "td", $j++ ) ) {
//                echo $item->text;
                if ( withClassName( $item->text ) ){
                    if( $type === 1){
                        $table[ $j-1 ][ $i-1 ] = convertDigit($item->text);
                    }else{
                        $table[ $i-1 ][ $j-1 ] = convertDigit($item->text);
                    }

                }else{
                    if( $type === 1){
                        $table[ $j-1 ][ $i-1 ] = $item->text;
                    }else{
                        $table[ $i-1 ][ $j-1 ] = $item->text;
                    }

                }
            }
        }
    }


    // 加上科任老師課表
    for( $i=0; $i < count($table); $i++ ){
        for($j=0; $j < count($table[$i]); $j++){
//            if( empty(table[$i][$j]) ) continue;
            foreach ( $teachers as $teacher ) {
                foreach ( $teacher as $teaid => $tea_name ) {

                    if ( mb_strpos( $table[ $i ][ $j ], $tea_name ) !== false ) {

                        $table[ $i ][ $j ] =   str_replace($tea_name, sprintf( '<a href=newClassTable.php?schoolid=%s&seltype=tea&teaid=%s>%s</a>', $_SESSION[ "schoolid" ], $teaid, $tea_name), $table[$i][$j] ) ;

                    }
                }
            }
        }
    }


    return $table;
}


function withTeacherName( $str ){
    return preg_match('/《([一-龜]+)》/is', $str);
}



function withClassName( $str ){
    return preg_match('/《(([一-龜]+)年([一-龜]+)班)》/is', $str);
}

function convertDigit($str){
    preg_match_all('/《(([一-龜]+)年([一-龜]+)班)》/is', $str, $matches);
    $classname_chi = $matches[1][0];
    $classname_digit = getChineseDigit($matches[2][0]) . zero(getChineseDigit($matches[3][0]), 2);

    $classname_digit = sprintf( '<a href="newClassTable.php?schoolid=%s&seltype=cls&year=%s&clsno=%s">%s</a>', $_SESSION['schoolid'], getChineseDigit($matches[2][0]), $classname_digit, $classname_digit);
    $classname = str_replace($classname_chi, $classname_digit, $str);

    return $classname;
}

function getChineseDigit($s, $prefix="")
{
    $ret = "";

    $numstringalpha = '0123456789';
    $numstringchinese1 = '○一二三四五六七八九十';
    $numstringchinese2 = '○忠孝仁愛信義和平';
    $numstringchinese3 = '○甲乙丙丁戊己庚辛壬癸';
    if ( preg_match( "/$s/is", $numstringchinese2 ) ) {
        $numstringchinese = $numstringchinese2;
    } elseif ( preg_match( "/$s/is", $numstringchinese3 ) ) {
        $numstringchinese = $numstringchinese3;
    } else {
        $numstringchinese = $numstringchinese1;
    }

    if ( (mb_strlen( $s ) == 1) and ($s === '十') ) {
            $ret .= '10';
    } else {

        for ( $i = 0; $i < mb_strlen( $s ); $i++ ) {
//        print mb_substr($s, $i, 1) . "<br />";
            $c = mb_substr( $s, $i, 1 );
            if ( $c === '十' ) {
                $ret .= '1';
            }
            $ret .= mb_substr( $numstringalpha, mb_strpos( $numstringchinese, $c ), 1 );
        }
    }

    if ( mb_strlen( $ret ) < 2 and mb_strlen( $ret ) > 0 ) {
        $ret = $prefix . $ret;
    }
    return $ret;
}

function zero( $str, $len=2 ){
    $zerostring = '00000000000000000000000000000000000000000';
    if( mb_strlen( $str) < $len ){
        $str = mb_substr($zerostring, 0, $len-mb_strlen($str)) . $str;
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
function array2table($array, $caption, $recursive = false, $null = '&nbsp;')
{
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
        $table .= "\t<tr>" ;
        foreach ($row as $cell) {
            $table .= '<td>';

            // Cast objects
            if (is_object($cell)) { $cell = (array) $cell; }

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


function isChi($str){
    return preg_match('/[一-龜]/is', $str);
}


function echobr($s){
    print "$s <br />";
}
