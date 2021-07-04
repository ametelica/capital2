<?php

// Жизненный цикл обменов-сотрудничества
function life($people, $options, $iterations = 50) {

    extract($options);

    $html = '';
    $log = [];

    arsort($people);

    //var_export($people);
    // считаем результаты сотрудничества


    $html .= '<table border=1 width=100% style="border-collapse: collapse">
        <tr>
            <th>Год</th>
            <th>Благосостояние</th>
            <th>50% богатства, %</th>
            <th>90% богатства, %</th>
            <th>Бедность, %</th>
            <th>Вынужденный труд</th>
            <th>Люди</th>
        </tr>';

    $forced_work_total = 0;

    for ($i = 0; $i < $iterations; $i++) {

        $log[] = "Год: " . $i;

        $results = [];
        $buzy = [];

        $sum = array_sum($people);
        $people_cnt = count($people);

        $forced_work = 0; // Вынужденный труд
        $sr = $sum / $people_cnt * $homeless_perc / 100;
        $dr = $sum / $people_cnt * $deadline_perc / 100;

        //echo 'DR = ' . $sr . "\n";

        //echo ($lazy_limit / 100 * count($people));
        //exit;

        foreach ($people as $k => $v) {

            if($v < $dr) {
                unset($people[$k]);
                $log[] = $k . ' (' . $v . ') погиб';
                continue;
            }

            if($k > ($lazy_limit / 100 * count($people))) { // Внимание, на связи лентяй!
                $is_lazy = 1;
                //echo 1;
                if($v > $sr) {
                    $log[] = $k . ' (' . $v . ') - Пропускаю, у меня и так всё есть!';
                    continue;
                } else {
                    $forced_work = 1;
                }
            } else {
                $is_lazy = 0;
            }

            // Выбрать партнёра с максимальным индексом
            $tmp = $people;
            unset($tmp[$k]);
            arsort($tmp);

            if($buzy[$k] >= $buzy_limit) {
                //log2('Я уже и так сотрудничаю');
                continue;
            }

            $buzy[$k]++;

            foreach($tmp as $partner => $ip) {
                // Этот партнёр уже занят
                if($buzy[$partner] >= $buzy_limit) {
                    //log2('Этот партнёр занят');
                    continue;
                }

                $buzy[$partner]++;

                //$result = round(sqrt($v * $ip) / 5);

                $idea = 1.1; // Ценность идеи
                $result = (($v + $ip) * $idea - ($v + $ip));

                $result_1 = round($v / ($v + $ip) * $result, 2);
                $result_2 = round($ip / ($v + $ip) * $result, 2);


                $log[] = $k . ' '. ($is_lazy ? '(лентяй)' : '') .' (' . $v . ') сотрудничает с ' . $partner . ' (' . $ip . "), результат " . $result . " (" . $result_1 . "/" .$result_2 . ")";


                if($forced_work) {
                    $forced_work_total += $result;
                }

                $results[$k] += $result_1;
                $results[$partner] += $result_2;
                break;
            }
            //$keys = array_keys($tmp);
            //$partner = $keys[0];
            //$ip = $tmp[$partner];
        }

        // Считаем налоги
        if($taxes > 0) {
            $taxes_total = 0;
            foreach($results as $k => $v) {
                $t = $v * $taxes/100;
                $taxes_total += $t;
                $results[$k] -= $t;
            }

            // Распределяем налоги
            $taxes_for_one = $taxes_total / $people_cnt;

            foreach($people as $k => $v) {
                $results[$k] += $taxes_for_one;
            }
        }

        // Добавляем производительность
        foreach($results as $k => $v) {
            $people[$k] = round($people[$k] + $v, 2);
        }

        $sum = round(array_sum($people));
        $people_cnt = count($people);

        $unround50 =  unround($people, 50);
        $unround90 =  unround($people, 90);
        $homeless = homeless($people, $homeless_perc);

        $log[] = 'Благосостояние: ' . $sum;
        $log[] = '50% богатства у ' . unround($people, 50) . "%";
        $log[] = '90% богатства у ' . unround($people, 90) . "%";
        $log[] = 'Бедность ' . homeless($people, $homeless_perc) . "%";
        $log[] = 'Люди ' . $people_cnt . "";

        $html .= '<tr>
            <td> ' . ($i + 1) . ' </td>
            <td> ' . round($sum) . ' </td>
            <td> ' . unround($people, 50) . ' </td>
            <td> ' . unround($people, 90) . ' </td>
            <td> ' . homeless($people, $homeless_perc) . ' </td>
            <td> ' . round($forced_work_total) . ' </td>
            <td> ' . $people_cnt . ' </td>		
        </tr>';

    }

    $html .= '</table>';

    return [
        'html' => $html,
        'sum' => round($sum),
        'unround50' => unround($people, 50),
        'unround90' => unround($people, 90),
        'homeless' => homeless($people, $homeless_perc),
        'log' => $log,
        'people_cnt' => count($people),
        'people' => $people,
    ];
}

// Генератор населения
function gen_people($options) {
    $people = [];
    for ($i = 0; $i < $options['people_cnt']; $i++) {
        $people[] = round(mt_rand($options['start_from_ip'], $options['start_to_ip']) / 10, 2) . '';
    }
    rsort($people);
    return $people;
}

// Неравенство
function unround($people, $perc) {
    arsort($people);

    //print_r($people);
    $sum = array_sum($people);
    $i = 0;
    $tmp = 0;

    foreach($people as $k => $v) {
        $tmp += $v;
        $i++;
        if($tmp >= ($sum * $perc / 100)) {
            return round($i / count($people) * 100);
        }
    }
}

// Процент людей за чертой бедности
function homeless($people, $perc) {
    $sr = array_sum($people) / count($people) * $perc / 100;
    $homeless = 0;
    foreach($people as $k => $v) {
        if($v < $sr) {
            $homeless++;
        }
    }
    return round($homeless / count($people) * 100);
}