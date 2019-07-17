<?php

// default
$data = array();

// get params
$type = $api->uri(1);
$endpoint = $api->uri(2);

// set modal
$api->setModal($type);

// check data
if ($endpoint == 'marca') {
    $data = $this->getMarks();
} elseif ($endpoint == 'familia') {
    $data = $this->getFamilies();
} elseif ($endpoint == 'total') {
    $totais = $this->getTotals();
    $valid = (!empty($totais) && is_array($totais) && key_exists(0, $totais)) && key_exists('preco_min', $totais[0]);
    $pr_min = (int) ($valid) ? min(\SmartDealer::array_column((array) $totais, 'preco_min')) : 0;
    $pr_max = (int) ($valid) ? max(\SmartDealer::array_column((array) $totais, 'preco_max')) : 0;
    $data = range($pr_min, $pr_max, 5000);
}

// output
die(json_encode($data));
