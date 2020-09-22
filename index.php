<?php
session_start();

include_once "vendor/autoload.php";

$query_url = 'https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp';

$schno = "";
$teaid = "";
$seltype = "";

$url_param = "";
$url = "";
if (isset($_POST["schoolid"])) {
    $schno = $_POST["schoolid"];
    $schno = str_replace(' ', ',', $schno);
    $schno = str_replace('/', ',', $schno);

    if (mb_strpos($schno, '國中') > 0 or mb_strpos($schno, '國小') > 0 or mb_strpos($schno, '國中小') > 0) {
        $school_info = get_school_info_by_name($schno);
        header('location:index.php?schoolid=' . $school_info[0]->id);
    }

    if (count($ar_schno = explode(',', $schno)) == 1 and substr($schno, 0, 3) != '014') {
        if (isChi($ar_schno[0])) {
            $seltype = 'tea';
            $tea_name = $ar_schno[0];
            $schno = $_SESSION['schoolid'];
            //$teachers = getData(file_get_contents($query_url . "?schno=$schno"), "teachers");
            $teachers = getData($query_url . "?schno=$schno", "teachers");

            //foreach ($teachers as $teacher) {
            foreach ($teachers as $key => $value) {
                if ($tea_name == $value) {
                    $teaid = $key;
                    break;
                }
            }
            //}
            $url_param = sprintf('schno=%s&seltype=%s&teaid=%s', $schno, $seltype, $teaid);
        } else {
            $seltype = 'cls';
            $schno = $_SESSION['schoolid'];
            $clsno = $ar_schno[0];
            $year = substr($clsno, 0, 1);
            $url_param = sprintf("schno=%s&seltype=cls&clsno=%s&year=%s", $schno, $clsno, $year);
        }
    } elseif (count($ar_schno = explode(',', $schno)) == 2) {
        $schno = $ar_schno[0];
        if (empty($schno)) {
            $schno = $_SESSION["schoolid"];
        }

        if (isChi($ar_schno[1])) {
            $seltype = 'tea';
            $tea_name = $ar_schno[1];

            $teachers = getData($query_url . "?schno=$schno", "teachers");

            foreach ($teachers as $key => $value) {
                if ($tea_name == $value) {
                    $teaid = $key;
                    break;
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
    $url = sprintf("https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?schno=%s&seltype=cls&year=%s&clsno=%s", $schno, $year, $clsno);
} elseif (isset($_GET["schoolid"])) {
    $schno = $_GET['schoolid'];
    $url = sprintf("https://esa.ntpc.edu.tw/jsp/lsnmgt_new/pub/index.jsp?schno=%s", $schno);
}

$_SESSION["schoolid"] = $schno;
if (empty($schno)) {
    $text['info'] = '請輸入學校名稱或代碼：';
    $text['placeholder'] = '例：幸福國小 or 014123';
} else {
    $text['info'] = '請輸入查詢班級或教師姓名：';
    $text['placeholder'] = '例：102 or 張大帥';
}
?>
<!Doctype html>
<html lang="zh-hant">

<head>
    <meta charset="UTF-8">
    <title>新北功課表查詢</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.9.1.js"></script>
    <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
    <link rel="stylesheet" href="http://jqueryui.com/resources/demos/style.css">
    <style>
    table {
        margin: 0.4em auto;
        border-collapse: collapse;
    }

    tr {
        height: 1.8em;
    }

    td {
        width: 100px;
        /* height: 100px; */
        border: 1px solid gray;
        text-align: center;
    }

    tr:first-child {
        background-color: #ddd;
    }

    td:first-child {
        background-color: #eee;
    }

    a {
        text-decoration: none;
    }

    a:link,
    a:visited {
        color: blue;
    }

    a:hover {
        background-color: blue;
        color: white;
    }

    .queryForm {
        margin: 0 auto;
        text-align: center;
        padding: 1em 1em;
        width: 590px;
        /*padding-top: 1em;*/
        /*padding-bottom: 1em;*/
        background-color: powderblue;
    }

    .queryForm input[type=text] {
        padding-left: 0.3em;
        padding-right: 0.3em;
        width: 250px;
    }

    .header p {
        text-align: right;
        margin: 0;
        padding: 0;
        font-size: 10pt;
    }

    .header a {
        text-decoration: none;

    }

    .container {
        width: 550px;
        margin: 0 auto;
    }

    .information {
        background-color: #DFF2BF;
        margin: .3em 0;
        padding: .3em 0;

    }

    .information:hover {
        background-color: darkgreen;
        color: #DFF2BF;
    }

    .footer {
        margin: 0 auto;

    }
    </style>
</head>

<body>
    <div class="queryForm">
        <?php
        $school_info = get_school_info($schno);
        if (is_array($school_info) and count($school_info)) {
            $school_info = $school_info[0];
        } else {
            $school_info = null;
        }
        ?>
        <?php if (isset($school_info->alias)): ?>
        <h3 style="margin:0;"><?= $school_info->alias ?> </h3>
        <hr>
        <?php endif; ?>
        <form method="POST" autocomplete="on">
            <?= $text['info'] ?>
            <input id="schoolid" name="schoolid" type="text" placeholder="<?= $text['placeholder'] ?>" autofocus />

            <input type="submit" value="查詢">
        </form>
    </div>
    <?php
    if (!isset($url) or empty($url)) {
        die();
    }

    $class_table = file_get_contents($url);
    $class_table = strip_tags($class_table, '<table><tr><td></td></tr></table><select><option></option></select>');

    $classes = getData($url, "classes");
    $teachers = getData($url, "teachers");

    $classtable_name = getClassTableName($class_table, $teaid = "");
    print array2table(getTable($url, $teachers), $classtable_name);

// end
?>
    <div class="container footer">
        <a href="changes.php" title="修訂記事">修訂記事(last modified: 2020/9/22)</a>
        <ul>
            <li>修正升級後無法執行的錯誤。</li>
            <li>表頭顯示查詢的學校名稱。</li>
            <li>查詢教師姓名時，可輸入部分文字後彈出選單列表。</li>
            <li>修正查詢教師時，任教班級若有第10班以後，班級名會無法正常顯示。</li>
        </ul>
    </div>

</body>
<script type="text/javascript">
var teacher_list = [ <?php echo implode(',', array_map('add_quotes', $teachers)); ?> ];
$(function() {
    $('#schoolid').autocomplete({
        source: teacher_list, //資料來源
        minLength: 1 //輸入最少字數
    });

    // 自動完成focus ; Click文字盒就顯示全部List
    $('#schoolid').focus(function() {
        if (this.value == "") {
            $(this).autocomplete("search");
        }
    });
});
</script>

</html>


<?php
// functions

function dump($obj)
{
    echo "<pre>";
    var_dump($obj);
    echo "</pre>";
}

function getClassTableName($content)
{
    $ret = '';
    try {
        preg_match_all('/([一-龜0-9]+) 　課表/is', $content, $matches);

        if (isset($matches[1][0])) {
            $ret = $matches[1][0];
        } else {
            preg_match_all('/([一-龜0-9]+ [一-龜0-9]+)　課表/is', $content, $matches);
            if (isset($matches[1][0])) {
                $ret = $matches[1][0];
            }
        }
    } catch (Exception $e) {
    }
    return $ret;
}

//function getData($content, $return_type = "classes")
function getData($url, $return_type = "classes")
{
    $ret = [];

    $dom = get_dom($url);

    $content = $dom->find('table')[0];

    if ($return_type === "classes") {
        // 各年級的班級數
        preg_match_all('/var (txtid[0-9]{1,2}) *= *"([^"]*)"/is', $content, $matches);
        foreach ($matches[2] as $value) {
            if (empty($value)) {
                continue;
            }

            $classes = explode(",", $value);
            $grade[substr($value, 0, 1)] = count($classes);
            $ret[substr($value, 0, 1)] = [substr($value, 0, 1) => $classes];
        }
    } elseif ($return_type === "teachers") {
        // 教師 id 與姓名

        $teachers_select_tag = $dom->find('#teaid', 0);

        $i = 0;
        $teachers = $teachers_select_tag->find("option");
        foreach ($teachers as $teacher) {
            if ($teacher->innerText) {
                $ret[$teacher->getAttribute("value")] = $teacher->innerText;
            }
        }
    }
    return $ret;
}

function showClassTable($content)
{
    $dom = new PHPHtmlParser\Dom();
    $dom->load($content);
    echo $dom->find('.lesson_word');
}

function getTable($url, $teachers, $type = 0)
{
    $dom = get_dom($url);

    $dom_class = $dom->find('.lesson_word');

    $i = 0;
    while ($row = $dom_class->find('tr')[$i++]) {
        $row_count = count($row->find("td"));

        if ($row_count <= 3) {
            if ($type === 1) {
                $table[0][$i - 1] = $row->find("td", 0)->innerText;
                for ($j = 2; $j <= 6; $j++) {
                    $table[$j - 1][$i - 1] = "";
                }
            } else {
                $table[$i - 1][0] = $row->find("td", 0)->innerText;
                for ($j = 2; $j <= 6; $j++) {
                    $table[$i - 1][$j - 1] = "";
                }
            }
        } else {
            $j = 0;
            while ($item = $row->find("td", $j++)) {
                if (withClassName($item->innerText)) {
                    if ($type === 1) {
                        $table[$j - 1][$i - 1] = convertDigit($item->innerText);
                    } else {
                        $table[$i - 1][$j - 1] = convertDigit($item->innerText);
                    }
                } else {
                    if ($type === 1) {
                        $table[$j - 1][$i - 1] = $item->innerText;
                    } else {
                        $table[$i - 1][$j - 1] = $item->innerText;
                    }
                }
            }
        }
    }

    // 加上科任老師課表
    for ($i = 0; $i < count($table); $i++) {
        for ($j = 0; $j < count($table[$i]); $j++) {
            foreach ($teachers as $teaid => $tea_name) {
                if (mb_strpos($table[$i][$j], $tea_name) !== false) {
                    $table[$i][$j] = str_replace($tea_name, sprintf('<a href=index.php?schoolid=%s&seltype=tea&teaid=%s>%s</a>', $_SESSION["schoolid"], $teaid, $tea_name), $table[$i][$j]);
                }
            }
        }
    }

    return $table;
}

function withTeacherName($str)
{
    return preg_match('/《([一-龜]+)》/is', $str);
}

function withClassName($str)
{
    return preg_match('/《(([一-龜]+)年([一-龜0-9]+)班)》/is', $str);
}

function convertDigit($str)
{
    preg_match_all('/《(([一-龜0-9]+)年([一-龜0-9]+)班)》/is', $str, $matches);
    $classname_chi = $matches[1][0];
    $classname_digit = getChineseDigit($matches[2][0]) . zero(getChineseDigit($matches[3][0]), 2);

    $classname_digit = sprintf('<a href="index.php?schoolid=%s&seltype=cls&year=%s&clsno=%s">%s</a>', $_SESSION['schoolid'], getChineseDigit($matches[2][0]), $classname_digit, $classname_digit);
    $classname = str_replace($classname_chi, $classname_digit, $str);

    return $classname;
}

function getChineseDigit($s, $prefix = "")
{
    $ret = "";
    $len = mb_strlen($s);
    $class_num = '';
    if (is_numeric($s)) {
        return zero($s, 2);
    }

    $numstringalpha = '0123456789';
    $numstringchinese1 = '○一二三四五六七八九十';
    $numstringchinese2 = '○忠孝仁愛信義和平';
    $numstringchinese3 = '○甲乙丙丁戊己庚辛壬癸';
    if (preg_match("/$s/is", $numstringalpha)) {
        $numstringchinese = $numstringalpha;
    } elseif (preg_match("/$s/is", $numstringchinese2)) {
        $numstringchinese = $numstringchinese2;
    } elseif (preg_match("/$s/is", $numstringchinese3)) {
        $numstringchinese = $numstringchinese3;
    } else {
        $numstringchinese = $numstringchinese1;
    }

    if ($len = 1) {
        $c = $s;
        if ($s === '十') {
            $class_num = '10';
        } else {
            $class_num = mb_substr($numstringalpha, mb_strpos($numstringchinese, $s), 1);
            // for ($i = 0; $i < $len; $i++) {
            //     //        print mb_substr($s, $i, 1) . "<br />";
            //     $c = mb_substr($s, $i, 1);
            //     if ($c === '十') {
            //         $ret .= '1';
            //     }
            //     $ret .= mb_substr($numstringalpha, mb_strpos($numstringchinese, $c), 1);
            // }
        }
    } elseif ($len = 2) {
        $ret = '1';
        $c = mb_substr($s, $len - 1, 1);
        $class_num = mb_substr($numstringalpha, mb_strpos($numstringchinese, $c), 1);
    }

    if (mb_strlen($class_num) == 1) {
        $ret .= $prefix . $class_num;
    }
    return $ret;
}

function zero($str, $len = 2)
{
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
function array2table($array, $caption, $recursive = false, $null = '&nbsp;')
{
    // Sanity check
    if (empty($array) || !is_array($array)) {
        return false;
    }

    if (!isset($array[0]) || !is_array($array[0])) {
        $array = [$array];
    }

    // Start the table
    $table = "<table>\n";

    //    // The Caption
    $table .= "\t<caption>$caption";
    $table .= "</caption>\n";

    // The body
    foreach ($array as $row) {
        $table .= "\t<tr>";
        foreach ($row as $cell) {
            $table .= '<td>';

            // Cast objects
            if (is_object($cell)) {
                $cell = (array) $cell;
            }

            if ($recursive === true && is_array($cell) && !empty($cell)) {
                // Recursive mode
                $table .= "\n" . array2table($cell, true, true) . "\n";
            } else {
                $table .=
                    strlen($cell) > 0
                        ? //                    htmlspecialchars((string) $cell) :
                        (string) $cell
                        : $null;
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
function isChi($str)
{
    return preg_match('/[一-龜]/is', $str);
}

/**
 * echo 函數變化版
 * @param $s
 */
function echobr($s)
{
    print "$s <br />";
}

/**
 * 由學校代號取得學校資訊
 *
 * @param string $id
 * @param string $return_type
 * @return mixed|null
 */
function get_school_info_by_id($id = '', $return_type = 'json')
{
    if ($id == "") {
        return null;
    }

    $query_url = 'https://data.ntpc.gov.tw/api/datasets/365714DB-5840-46E6-AD3F-CD5BF6460D20/json?$format=json';

    $query_url .= '&$filter=' . rawurlencode(sprintf('id eq %s', $id));

    $JSON = json_decode(file_get_contents($query_url));

    return $JSON;
}

/**
 * 由學校名稱取得學校資訊
 *
 * @param string $name
 * @param string $return_type
 * @return mixed|null
 */
function get_school_info_by_name($name = '', $return_type = 'json')
{
    if ($name == "") {
        return null;
    }

    $query_url = 'https://data.ntpc.gov.tw/api/datasets/365714DB-5840-46E6-AD3F-CD5BF6460D20/json?$format=json';

    $query_url .= '&$filter=' . rawurlencode(sprintf('alias eq %s', $name));

    $JSON = json_decode(file_get_contents($query_url));

    return $JSON;
}

function get_dom($url = '')
{
    if (!isset($GLABOLS['dom']) or !is_object($GLOBALS['dom'])) {
        $GLOBALS['dom'] = new PHPHtmlParser\Dom();
        $GLOBALS['dom']->loadFromUrl($url);
    }
    return $GLOBALS['dom'];
}

function add_quotes($str)
{
    return sprintf("'%s'", $str);
}

function get_school_info($schoolid)
{
    if (!isset($_SESSION[$schoolid])) {
        $_SESSION[$schoolid] = get_school_info_by_id($schoolid);
    }

    return $_SESSION[$schoolid];
}