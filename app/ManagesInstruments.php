<?php

namespace App;

trait ManagesInstruments
{
    public static $instrumentGroups = [
        'woodwind' => [
            'name' => 'Hohes Holz',
            'instruments' => ['Flöte', 'Oboe', 'Fagott'],
        ],
        'clarinet' => [
            'name' => 'Klarinette',
            'instruments' => ['Klarinette', 'Bass Klarinette'],
        ],
        'sax' => [
            'name' => 'Sax',
            'instruments' => ['Alt', 'Tenor', 'Bariton'],
        ],
        'trumpet' => [
            'name' => 'Trompete',
            'instruments' => ['Trompete', 'Cornette', 'Flügelhorn'],
        ],
        'horn' => [
            'name' => 'Horn',
            'instruments' => ['Horn'],
        ],
        'brass' => [
            'name' => 'Tiefes Blech',
            'instruments' => ['Posaune', 'Bariton', 'Euphonium', 'Tuba'],
        ],
        'drums' => [
            'name' => 'Schlagwerk',
            'instruments' => ['Schlagwerk'],
        ],
        'other' => [
            'name' => 'Sonstiges',
            'instruments' => ['Klavier', 'Gitarre', 'E-Bass'],
        ],
    ];
}
