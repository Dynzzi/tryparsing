<?php
trait Trait1{
    public function executeQuery ($con,$query){
        try {
//            $statement = $this->connection->prepare($query);
//            $statement->execute();
            $con->query($query);

        } catch (PDOException $e) {
            echo "Request failed: " . $e->getMessage();
        }
    }

}
class Database {
    use Trait1;
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $connection;

    private static ?Database $instance = null;
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct(){}

    private function __clone(){}
    public function __wakeup(){}
    public function set_attr($servername,$dbname, $username, $password) {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname=$dbname;
    }

    public function tryconnect() {
        try {
            $this->connection = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);
        } catch (PDOException $e) {
            echo 'Connection failed2: ' . $e->getMessage();
            die();
        }
    }
    public function get_conn()
    {
        if($this->connection!=null){
            return $this->connection;
        }else{echo 'No connection to the base';}
    }
    public function close_con() {
        $this->connection = null;
    }
    public function executeQuery($query){
        try {
//            $statement = $this->connection->prepare($query);
//            $statement->execute();
            $this->connection->query($query);

        } catch (PDOException $e) {
            echo "Request failed: " . $e->getMessage();
            die();

        }
    }
}
class Parser{
    use Trait1;

    private $html;
    public function __construct($url='')
    {
        if(preg_match("/^https:\/\/101hotels\.com.*/",$url)){
            $url='https://101hotels.com/opinions/hotel/volzhskiy/gostinitsa_ahtuba.html';
            $this->html = file_get_html($url);
        }else{
            echo "I don't know about this site";
            die();
        }
    }
    public function pars($con)
    {
        foreach($this->html->find('li.review-item') as $tr) {

            $regex = '/<div class="reviewer">.*<\/div>\s*(.*)\s*<div class="review-date">/';
            if (preg_match($regex, substr($tr, 0, 700), $matches_login)) {

                $login =$matches_login[1];
            }
            $regex = '/<div class="review-date">(.*?)<\/div>/';
            if (preg_match($regex, substr($tr, 0, 700), $matches_date)) {
                $date=$matches_date[1];

            }
            $regex = '/<div class="review-pro">.*?<\/span>([\s\S]*?)<\/div>/';
            try {preg_match_all($regex, $tr, $matches_positive);
                $positive=implode($matches_positive[1]);
            }catch (Exception){}
            $regex = '/<span class="review-score">([0-9.]+)<\/span>/';
            try {preg_match_all($regex, $tr, $matches_score);
                $score=implode($matches_score[1]);
            }catch (Exception){}

            $regex = '/<div class="review-contra"><span class="fa fa-thumbs-down review_minus"><\/span>(.*?)<\/div>/';
            try {preg_match_all($regex, $tr, $matches_negative);
                $negative=implode($matches_negative[1]);
            }catch (Exception){}
            $positive_negative = $positive . '\n' . $negative;


            $query = "INSERT INTO reviewer (name, date, score, text) VALUES ('$login','$date','$score', '$positive_negative')";
            if (!empty($matches_negative[1]) or !empty($matches_positive[1])) {
                $this->executeQuery($con,$query);
            }
            unset($matches_login, $matches_date, $matches_negative, $matches_positive, $matches_score, $positive, $negative);
            unset($login, $date, $score, $positive_negative);
            unset ($query);
        }
        $header_data = $this->html->find('.total-rating', 0);
        $pattern = '/<span class="score" itemprop="ratingValue">([0-9.]+)<\/span>/';

        if (preg_match($pattern, $header_data, $rating_data)) {
            $rating = $rating_data[1];
        }

        $pattern = '/<span>(\d+)<\/span> отзыва и/';

        if (preg_match($pattern, $header_data, $reviews_data)) {
            $reviews = $reviews_data[1];
        }

        $pattern = '/<span>(\d+)<\/span> оценок/';

        if (preg_match($pattern, $header_data, $score_data)) {
            $score = $score_data[1];
        }
        $query = "INSERT INTO rating_all (rating, reviewer_count, score_count) VALUES ('$rating', '$reviews', '$score')";
        $this->executeQuery($con,$query);;

    }
}