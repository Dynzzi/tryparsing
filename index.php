<?php
include'simple_html_dom.php';
include 'Сlasses.php';

$db = Database::getInstance();

$db->set_attr('127.0.0.1','tryparsing', 'root', '');
$db->tryconnect();
//////$db->executeQuery('CREATE DATABASE IF NOT EXISTS tryparsing');
$db->executeQuery('DROP TABLE IF EXISTS reviewer,rating_all');
$db->executeQuery('CREATE TABLE IF NOT EXISTS reviewer(name VARCHAR(50) ,date VARCHAR(50), score VARCHAR(50),text VARCHAR(1000))');
$db->executeQuery('CREATE TABLE IF NOT EXISTS rating_all(rating FLOAT ,reviewer_count INT, score_count INT)');
//$db->executeQuery('DROP TABLE IF EXISTS reviewer,rating_all');

$par= new Parser('https://101hotels.com/opinions/hotel/volzhskiy');
$con=$db->get_conn();
$par->pars($con);
$db2 = Database::getInstance();
var_dump($db===$db2);
$db->close_con();