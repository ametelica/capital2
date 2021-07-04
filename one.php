<?php

//ini_set('display_errors', 1);

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

// Задаём начальные значения людей с индексами производительности

include "functions.php";
include "people.php";

echo '<pre>';

$a = life($people, $options, 50);

print_r($a['sum']);
exit;

echo $a['html'];
print_r($a['log']);
exit;