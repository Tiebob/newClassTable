<!Doctype html>
<html lang="zh-tw-hant">

<head>
    <meta charset="UTF-8">
    <title>修訂記事</title>
    <style>
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
    </style>
</head>

<body>
    <div class="container header">
        <h1>修訂記事</h1>
        <p><a href="index.php">課表查詢</a></p>
    </div>
    <div class="container content">

        <ul>
            <li>
                <div class="information">
                    2020.9.22: 修正系統升級至 php 7.4 後，無法正常執行的問題。
                    <ul>
                        <li>修正升級後無法執行的錯誤。</li>
                        <li>表頭顯示查詢的學校名稱。</li>
                        <li>查詢教師姓名時，可輸入部分文字後彈出選單列表。</li>
                        <li>修正查詢教師時，任教班級若有第10班以後，班級名會無法正常顯示。</li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="information">2016/10/30: 新增輸入學校名稱(ex:淡水國小)也可以查詢</div>
            </li>
            <li>
                <div class="information">2016/10/28: 修正學校代號無法一直記憶的問題</div>
            </li>
            <li>
                <div class="information">2015/10/28: 修正班級會出現不正確的問題</div>
            </li>
        </ul>
    </div>
</body>

</html>