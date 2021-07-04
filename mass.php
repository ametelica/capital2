<?php

ini_set("max_execution_time", 300);

$options = [
    'people_cnt'    => 100,  // количество людей в модели
    'start_from_ip' => 100, // минимальный индекс производительности
    'start_to_ip'   => 300, // максимальный индекс производительности
    'lazy_limit'    => 50,  // все, кто после этой цифры - лентяи, и  будут работать только под угрозой бедности$taxes         = 0,
    'homeless_perc' => 10,  // процент от среднего благосостояние, ниже которого начинается бедность
    'deadline_perc' => 3,   // процент, ниже которого уже не выжить
    'buzy_limit'    => 3,   // столько партнёров можно одновременно иметь в работе
    'taxes'         => 5,  // процент налогов
];

include "functions.php";
include "people.php";

$head_html = '<th>Налоги\Лентяи</th>';
$body_html = '';

$lazy_max = 100;

for($lazy = 0; $lazy <= $lazy_max; $lazy+=10) {
    $head_html .= '<th>' . (100 - $lazy) . '</th>';
}

$min_sum = 9999999;
$max_sum = 0;
$sum1 = [];
$taxes_step = 5;

for($taxes = 0; $taxes <= 100; $taxes+=$taxes_step) {
    //$head_html .= '<th>' . $taxes . '</th>';
    $body_html .= '<tr><th>' . $taxes . '%</th>';

    for($lazy = 0; $lazy <= $lazy_max; $lazy+=10) {
        $options['taxes'] = $taxes;
        $options['lazy_limit'] = $lazy;

        $a = life($people, $options, 50);

        if($a['sum'] < $min_sum) {
            $min_sum = $a['sum'];
        }

        if($a['sum'] > $max_sum) {
            $max_sum = $a['sum'];
        }

        $sum1[$taxes][$lazy] = $a['sum'];
        $body_html .= '<td>' . $sum1[$taxes][$lazy] . '</td>';
    }
    $body_html .= '</tr>';
}

// Расчёт
echo '<table><tr>' . $head_html . '</tr>' . $body_html . '</table>';

// Диаграмма
$body_html = '';
for($taxes = 0; $taxes <= 100; $taxes+=$taxes_step) {
    $body_html .= '<tr><th>' . $taxes . '%</th>';
    for($lazy = 0; $lazy <= $lazy_max; $lazy+=10) {
        $g = round( $sum1[$taxes][$lazy] / $max_sum * 255);
        $body_html .= '<td style="background: rgb('. (255 - $g) .', ' . $g . ', 0)">' . round($sum1[$taxes][$lazy] / $min_sum) . '</td>';
    }
    $body_html .= '</tr>';
}
echo '<table><tr>' . $head_html . '</tr>' . $body_html . '</table>';

echo $max_sum;