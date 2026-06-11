<?php

namespace TheRealMchaggis\DefinitionTypes\Types;

class Month extends Picklist
{
    public string $type      = 'month';
    public string $display   = 'Month';
    public ?string $base_type = 'picklist';
    public array $validation = [];
    public array $meta_options = [];

    public function __construct()
    {
        $this->validation['in_list'] = [];
        foreach ($this->getValues() as $value) {
            $this->validation['in_list'][] = $value->value;
        }
    }

    public function getValues(...$params): array
    {
        return [
            (object) ['ID' => '01', 'value' => 'january',   'display' => 'January'],
            (object) ['ID' => '02', 'value' => 'february',  'display' => 'February'],
            (object) ['ID' => '03', 'value' => 'march',     'display' => 'March'],
            (object) ['ID' => '04', 'value' => 'april',     'display' => 'April'],
            (object) ['ID' => '05', 'value' => 'may',       'display' => 'May'],
            (object) ['ID' => '06', 'value' => 'june',      'display' => 'June'],
            (object) ['ID' => '07', 'value' => 'july',      'display' => 'July'],
            (object) ['ID' => '08', 'value' => 'august',    'display' => 'August'],
            (object) ['ID' => '09', 'value' => 'september', 'display' => 'September'],
            (object) ['ID' => '10', 'value' => 'october',   'display' => 'October'],
            (object) ['ID' => '11', 'value' => 'november',  'display' => 'November'],
            (object) ['ID' => '12', 'value' => 'december',  'display' => 'December'],
        ];
    }
}
