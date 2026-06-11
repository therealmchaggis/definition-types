<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

class DayOfWeek extends Picklist
{
    public string $type       = 'dayofweek';
    public string $display    = 'Day of Week';
    public ?string $base_type = 'picklist';
    public array $validation  = [
        'in_list' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
    ];

    public function getValues(...$params): array
    {
        return [
            (object) ['value' => 'monday',    'display' => 'Monday'],
            (object) ['value' => 'tuesday',   'display' => 'Tuesday'],
            (object) ['value' => 'wednesday', 'display' => 'Wednesday'],
            (object) ['value' => 'thursday',  'display' => 'Thursday'],
            (object) ['value' => 'friday',    'display' => 'Friday'],
            (object) ['value' => 'saturday',  'display' => 'Saturday'],
            (object) ['value' => 'sunday',    'display' => 'Sunday'],
        ];
    }
}
