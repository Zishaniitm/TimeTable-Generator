<?php
/**
 * Round-robin scheduler (PHP port of the original TypeScript version).
 * Cycles through subjects one hour at a time and always places the next block on the
 * least-loaded day that does not already hold that subject (falling back to any open day).
 *
 * @param array $subjects [['id'=>int,'name'=>string,'hours_per_week'=>int], ...]
 * @return array ['slots' => [['subject_id','day_of_week','start_hour'], ...], 'unplaced' => int]
 */
function generate_timetable(array $subjects, array $days, int $startHour, int $endHour): array {
    $perDay = max(0, $endHour - $startHour);
    $days = array_values(array_unique($days));
    sort($days);
    $load = array_fill_keys($days, 0);
    $subjectDays = [];

    $queue = array_values(array_filter($subjects, fn($s) => $s['hours_per_week'] > 0));
    usort($queue, fn($a, $b) => ($b['hours_per_week'] <=> $a['hours_per_week']) ?: strcmp($a['name'], $b['name']));
    foreach ($queue as &$q) { $q['left'] = (int)$q['hours_per_week']; }
    unset($q);

    $slots = [];
    $capacity = count($days) * $perDay;

    while (count($slots) < $capacity && array_sum(array_column($queue, 'left')) > 0) {
        $placed = false;
        foreach ($queue as &$item) {
            if ($item['left'] <= 0) continue;
            $open = array_values(array_filter($days, fn($d) => $load[$d] < $perDay));
            if (!$open) break;

            $taken = $subjectDays[$item['id']] ?? [];
            $preferred = array_values(array_filter($open, fn($d) => !isset($taken[$d])));
            $pool = $preferred ?: $open;
            usort($pool, fn($a, $b) => ($load[$a] <=> $load[$b]) ?: ($a <=> $b));
            $day = $pool[0];

            $slots[] = ['subject_id' => (int)$item['id'], 'day_of_week' => $day, 'start_hour' => $startHour + $load[$day]];
            $load[$day]++;
            $subjectDays[$item['id']][$day] = true;
            $item['left']--;
            $placed = true;
        }
        unset($item);
        if (!$placed) break;
    }

    return ['slots' => $slots, 'unplaced' => (int)array_sum(array_map(fn($q) => max(0, $q['left']), $queue))];
}

const SUBJECT_COLORS = ['#2f7fb5', '#2fa08a', '#5b8def', '#3fa66a', '#7d6ff0', '#d98a3a', '#c0607f', '#4aa3c7'];
