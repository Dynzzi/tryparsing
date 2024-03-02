<?php
include('simple_html_dom.php');
// Подключение к базе данных

$db = mysqli_connect('127.0.0.1', 'root', '', 'localdb')
or die('Error in established MySQL-server connect');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Ошибка подключения к базе данных: " . $e->getMessage();
}

// Получение основных данных организации
$url = 'https://101hotels.com/opinions/hotel/volzhskiy/gostinitsa_ahtuba.html';
$html = file_get_html($url);
//$rating = $html->find('.review-item', 0);
foreach($html->find('li.review-item') as $tr){
    /////////////
    $regex = '/<div class="reviewer">.*<\/div>\s*(.*)\s*<div class="review-date">/';
    if (preg_match_all($regex, substr($tr, 0, 700), $matches_login)) {
//    print_r($matches_login[1]);
        $login = implode($matches_login[1]);
    }
    $regex = '/<div class="review-date">(.*?)<\/div>/';
    if (preg_match_all($regex, substr($tr, 0, 700), $matches_date)) {
//    print_r($matches_date[1]);
        $date = implode($matches_date[1]);
    }
    $regex = '/<div class="review-pro">.*?<\/span>([\s\S]*?)<\/div>/';
    if (preg_match_all($regex, $tr, $matches_positive)) {
//    print_r($matches_positive[1]);
        $positive = implode($matches_positive[1]);
    }
    $regex = '/<span class="review-score">([0-9.]+)<\/span>/';
    if (preg_match_all($regex, $tr, $matches_score)) {
//    print_r($matches_score[1]);
        $score = implode($matches_score[1]);
    }
    $regex = '/<div class="review-contra"><span class="fa fa-thumbs-down review_minus"><\/span>(.*?)<\/div>/';
    if (preg_match_all($regex, $tr, $matches_negative)) {
//    print_r($matches_negative[1]);
        $negative = implode($matches_negative[1]);
    }
    $positive_negative = $positive . '\n' . $negative;

    $query = "INSERT INTO organizations (NAME, DATE, SCORE, TEXT) VALUES ('$login', '$date', '$score', '$positive_negative')";
    IF (!empty($matches_negative[1]) OR !empty($matches_positive[1]))  {
        $result = mysqli_query($db, $query);}

    unset($matches_login, $matches_date, $matches_negative, $matches_positive, $matches_score, $positive, $negative);
    unset($login, $date, $score, $positive_negative);
    unset ($query);
//    $gt = $tr->find('.reviewer');
//    print ($gt[0]);
//    $bc = $gt->find('');
//echo $tr;
//print_r("\n");
//        echo $tr;
    // //////////
}

$header_data = $html->find('.total-rating', 0);
$pattern = '/<span class="score" itemprop="ratingValue">([0-9.]+)<\/span>/';

if (preg_match_all($pattern, $header_data, $rating_data)) {
//    print_r($matches_date[1]);
    $rating = implode($rating_data[1]);
}

$pattern = '/<span>(\d+)<\/span> отзыва и/';

if (preg_match_all($pattern, $header_data, $reviews_data)) {
//    print_r($matches_date[1]);
    $reviews = implode($reviews_data[1]);
}

$pattern = '/<span>(\d+)<\/span> оценок/';

if (preg_match_all($pattern, $header_data, $score_data)) {
//    print_r($matches_date[1]);
    $score = implode($score_data[1]);
}
$query = "INSERT INTO common_data (RATING, REVIEWS_COUNT, SCORE_COUNT) VALUES ('$rating', '$reviews', '$score')";
$result = mysqli_query($db, $query) or die ('Error in query to database');
mysqli_close($db);