<?php

namespace TheRealMchaggis\DefinitionTypes\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\BinaryNode;
use Symfony\Component\ExpressionLanguage\Node\ConditionalNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use Symfony\Component\ExpressionLanguage\Node\FunctionNode;
use Symfony\Component\ExpressionLanguage\Node\GetAttrNode;
use Symfony\Component\ExpressionLanguage\Node\NameNode;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * Compiles a Symfony ExpressionLanguage expression into an equivalent MySQL
 * expression, used by the optional `calculated` data type when building export
 * SQL. Part of the package's optional expression feature; only loaded when
 * symfony/expression-language is installed.
 */
class ExpressionLanguageToSQL
{
    protected ExpressionLanguage $language;

    public function __construct(?ExpressionLanguage $language = null)
    {
        $this->language = $language ?? ExpressionLanguageFactory::instance();
    }

    public function convert(string $expression, $vars = []): string
    {
        try {
            // Parse the expression to AST
            $parsed = $this->language->parse($expression, $vars);

            return $this->compileNode($parsed->getNodes());
        } catch (SyntaxError $e) {
            throw new \InvalidArgumentException('Expression syntax error: ' . $e->getMessage());
        }
    }

    protected function compileNode($node): string
    {
        return match (true) {
            $node instanceof BinaryNode => $this->compileBinary($node),
            $node instanceof NameNode => "`{$node->attributes['name']}`",
            $node instanceof ConstantNode => is_string($node->attributes['value'])
                ? "'" . addslashes($node->attributes['value']) . "'"
                : (string) $node->attributes['value'],
            $node instanceof ConditionalNode => $this->compileConditional($node),
            $node instanceof FunctionNode => $this->compileFunction($node),
            $node instanceof GetAttrNode => $this->compileGetAttr($node),
            default => throw new \LogicException('Unsupported node type: ' . get_class($node)),
        };
    }

    protected function compileBinary($node): string
    {
        $left  = $this->compileNode($node->nodes['left']);
        $right = $this->compileNode($node->nodes['right']);
        $op    = $node->attributes['operator'];

        // Convert the operators
        $operatorMap = [
            '===' => '=',
            '==' => '=',
            '!==' => '!=',
        ];
        $op = str_replace(array_keys($operatorMap), array_values($operatorMap), $op);

        return "($left $op $right)";
    }

    protected function compileConditional($node): string
    {
        $cond    = $this->compileNode($node->nodes['expr1']);
        $ifTrue  = $this->compileNode($node->nodes['expr2']);
        $ifFalse = $this->compileNode($node->nodes['expr3']);

        return "(CASE WHEN $cond THEN $ifTrue ELSE $ifFalse END)";
    }

    protected function compileFunction($node): string
    {
        $name = $node->attributes['name'];
        $args = array_map([$this, 'compileNode'], $node->nodes['arguments']->nodes);

        return match ($name) {
            'today' => 'CURRENT_DATE',
            'date' => count($args) === 0
                ? 'CURRENT_DATE'
                : "STR_TO_DATE({$args[0]}, '%Y-%m-%d H:i:s')",

            'if' => "IF( {$args[0]}, {$args[1]},{$args[2]})",
            'addDays' => "DATE_ADD(CURRENT_DATE, INTERVAL {$args[0]} DAY)",

            'next' => "DATE_ADD(CURRENT_DATE, INTERVAL (7 + DAYOFWEEK(STR_TO_DATE({$args[0]}, '%W')) - DAYOFWEEK(CURRENT_DATE)) % 7 DAY)",

            default => throw new \LogicException("Unsupported function: $name"),
        };
    }

    protected function compileGetAttr($node): string
    {
        $object         = $this->compileNode($node->nodes['node']);
        $attr           = $node->nodes['attribute'];
        $parentfunction = $node->nodes['node']->attributes['name'];
        if (in_array($parentfunction, ['date', 'today', 'addDays', 'next'], true)) {
            $parentfunction = 'date';
        }
        // Handle method calls like date().format("m")
        if ($node->attributes['type'] === GetAttrNode::METHOD_CALL) {
            $method   = $attr->attributes['value'];
            $args     = array_map([$this, 'compileNode'], $node->nodes['arguments']->nodes);
            $args[1]  = trim($args[1] ?? '', '\'"');
            $funct    = "parse_{$parentfunction}_{$method}";
            if (method_exists($this, $funct)) {
                return $this->$funct($object, $args[1]);
            }

            throw new \LogicException("Unsupported method call: $method");
        }

        throw new \LogicException("Unsupported attribute access type: {$node->attributes['type']}");
    }

    public function parse_date_format($object, string $phpFormat): string
    {
        $map = [
            'd' => '%d',   // Day with leading zero
            'j' => '%e',   // Day without leading zero
            'm' => '%m',   // Month with leading zero
            'n' => '%c',   // Month without leading zero
            'Y' => '%Y',   // 4-digit year
            'y' => '%y',   // 2-digit year
            'H' => '%H',   // 24-hour format
            'h' => '%h',   // 12-hour format
            'i' => '%i',   // Minutes
            's' => '%s',   // Seconds
            'A' => '%p',   // AM/PM
            'l' => '%W',   // Full weekday name
            'D' => '%a',   // Abbreviated weekday name
            'F' => '%M',   // Full month name
            'M' => '%b',   // Abbreviated month name
            'U' => '%s',   // Unix timestamp
        ];

        // Replace each PHP format char with its MySQL equivalent
        $mysql  = '';
        $length = strlen($phpFormat);
        for ($i = 0; $i < $length; $i++) {
            $char = $phpFormat[$i];
            $mysql .= $map[$char] ?? $char;
        }

        return 'DATE_FORMAT(' . $object . ', "' . $mysql . '")';
    }
}