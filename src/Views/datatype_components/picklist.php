<?php

use TheRealMchaggis\DefinitionTypes\DataTypeRegistry;

$list = $list ?? [];
if (isset($data_type) && is_object($data_type)) {
    $list = $data_type->getValues();
} else {
    if (! is_array($list ?? [])) {
        $params = explode(',', $list);
        array_walk($params, static function (&$value, $k) {
            if ($k) {
                parse_str($value, $value);
            }
        });
        $obj = DataTypeRegistry::instance()->get('picklist');
        if (isset($schema_id)) {
            $params[1]['schema_id'] = $schema_id;
        }
        $list = call_user_func_array([$obj, 'getValues'], $params);
    }
}
?>

<select
    id="<?= esc($id ?? '') ?>"
    name="data[<?= esc($name ?? '') ?>]"
    title="<?= esc($display ?? '') ?>"
    <?= is_required($validation ?? '') ? 'required' : '' ?>
    class="form-control"
    data-placeholder="<?= esc($meta['placeholder'] ?? '') ?>"
    data-value="<?= esc($value ?? '') ?>"
>
    <?php if (empty($required)) : ?>
        <option value=""><?= esc($display_optional ?? '') ?></option>
    <?php endif ?>
    <?php foreach (array_keys($list) as $key) : ?>

        <option value="<?= esc($list[$key]->value ?? $key) ?>"

            <?php if (($list[$key]->value ?? $key) === ($value ?? '')) : ?>
                selected="selected"
            <?php endif ?>

        >
            <?php if (is_object($list[$key])) : ?>
                <?= $list[$key]->display ?? $list[$key]->value ?? $key ?>
            <?php else : ?>
                <?= $list[$key] ?? $key ?>
            <?php endif ?>
        </option>

    <?php endforeach; ?>
</select>
