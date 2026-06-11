<?php
/** @var string $id */
/** @var string $name */

use TheRealMchaggis\DefinitionTypes\DataTypeRegistry;

$native_type = $native_type ?? 'text';
$type        = DataTypeRegistry::instance()->get($native_type);
?>
<span class="form-control disabled">
<?= ($type !== null)
    ? $type->getFormated(
        $data[$id] ?? $data[$name] ?? '',
        $data ?? []
    )
    : format_value(
        $data[$id] ?? $data[$name] ?? '',
        $field ?? ''
    ) ?>
</span>
