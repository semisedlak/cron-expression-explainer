<?php declare(strict_types = 1);

return [
	'listSeparator' => ', ',
	'list' => '{values} a {lastValue}',
	'step-all-minute' => 'každou {step}. minutu',
	'step-all-hour' => 'každou {step}. hodinu',
	'step-all-day-of-week' => 'každý {step}. den v týdnu',
	'step-all-day-of-month' => 'každý {step}. den v měsíci',
	'step-all-month' => 'každý {step}. měsíc',
	'step-minute' => 'každou {step}. minutu {part}',
	'step-hour' => 'každou {step}. hodinu {part}',
	'step-day-of-week' => 'každý {step}. den v týdnu {part}',
	'step-day-of-month' => 'každý {step}. den v měsíci {part}',
	'step-month' => 'každý {step}. měsíc {part}',
	'range-minute' => 'od {left} do {right}',
	'range-minute-named' => 'každou minutu od {left} do {right}',
	'range-hour' => 'od {left} do {right}',
	'range-hour-named' => 'každou hodinu od {left} do {right}',
	'range-day-of-week' => 'od {left} do {right}',
	'range-day-of-week-named' => 'každý den v týdnu od {left} do {right}',
	'range-day-of-month' => 'od {left} do {right}',
	'range-day-of-month-named' => 'každý den v měsíci od {left} do {right}',
	'range-month' => 'od {left} do {right}',
	'range-month-named' => 'každý měsíc od {left} do {right}',
	'second' => '{second, plural,
      one {každou sekundu}
      few {každé # sekundy}
      other {každých # sekund}
    }',
	'every-minute' => 'každou minutu',
	'before-minute' => '',
	'minute' => '{minute}',
	'minute-named' => 'v minutě {minute}',
	'before-hour' => ' ',
	'hour' => '{hour}',
	'hour-named' => 'v hodině {hour}',
	'between-day-of-month-and-week' => ' a',
	'before-day-of-week' => '{dayNumber, select,
	  3 { ve }
	  4 { ve }
      other { v }
	}',
	'day-of-week' => '{context, select,
      step {{dayNumber, select,
        1 {pondělí}
        2 {úterý}
        3 {středy}
        4 {čtvrtka}
        5 {pátku}
        6 {soboty}
        7 {neděle}
        other {{dayNumber} - unknown}
      }}
      range {{dayNumber, select,
        1 {pondělí}
        2 {úterý}
        3 {středy}
        4 {čtvrtka}
        5 {pátku}
        6 {soboty}
        7 {neděle}
        other {{dayNumber} - unknown}
      }}
      other {{dayNumber, select,
        1 {pondělí}
        2 {úterý}
        3 {středu}
        4 {čtvrtek}
        5 {pátek}
        6 {sobotu}
        7 {neděli}
        other {{dayNumber} - unknown}
      }}
    }',
	'day-of-week-nth' => '{nth}. {day}',
	'day-of-week-last' => '{context, select,
      range {{dayNumber, select,
        1 {poslední pondělí}
        2 {poslední úterý}
        3 {poslední středy}
        4 {posledního čtvrtka}
        5 {posledního pátku}
        6 {poslední soboty}
        7 {poslední neděle}
        other {{day} - unknown}
      }}
      other {poslední {day}}
    }',
	'before-day-of-month' => ' ',
	'day-of-month' => '{day}',
	'day-of-month-named' => 've dni v měsíci {day}',
	'day-of-month-last-day' => '{context, select,
      range {posledního dne v měsíci}
      other {v poslední den v měsíci}
    }',
	'day-of-month-last-weekday' => '{context, select,
      range {posledního pracovního dne}
      other {v poslední pracovní den}
    }',
	'day-of-month-nearest-weekday' => '{context, select,
      range {pracovního dne nejbližšího k {day}.}
      other {v pracovním dni nejbližším k {day}.}
    }',
	'before-month' => ' v ',
	'month' => '{context, select,
      step {{month, select,
        1 {ledna}
        2 {února}
        3 {března}
        4 {dubna}
        5 {května}
        6 {června}
        7 {července}
        8 {srpna}
        9 {září}
        10 {října}
        11 {listopadu}
        12 {prosince}
        other {{month} - unknown}
      }}
      range {{month, select,
        1 {ledna}
        2 {února}
        3 {března}
        4 {dubna}
        5 {května}
        6 {června}
        7 {července}
        8 {srpna}
        9 {září}
        10 {října}
        11 {listopadu}
        12 {prosince}
        other {{month} - unknown}
      }}
      other {{month, select,
        1 {lednu}
        2 {únoru}
        3 {březnu}
        4 {dubnu}
        5 {květnu}
        6 {červnu}
        7 {červenci}
        8 {srpnu}
        9 {září}
        10 {říjnu}
        11 {listopadu}
        12 {prosinci}
        other {{month} - unknown}
      }}
    }',
	'hour+minute' => '{hourNumeric, select,
      2 {ve}
      3 {ve}
      4 {ve}
      12 {ve}
      13 {ve}
      14 {ve}
      22 {ve}
      23 {ve}
      other {v}
    } {hourNumeric}:{minute}',
	'day-of-month+month' => '{day}. {month, select,
      1 {ledna}
      2 {února}
      3 {března}
      4 {dubna}
      5 {května}
      6 {června}
      7 {července}
      8 {srpna}
      9 {září}
      10 {října}
      11 {listopadu}
      12 {prosince}
      other {{month} - unknown}
    }',
	'timezone' => 'v časové zóně {tz}',
];
