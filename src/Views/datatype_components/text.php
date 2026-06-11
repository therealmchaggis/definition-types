<input
    id="<?= esc($id ?? '') ?>"
    name="data[<?= esc($name ?? '') ?>]"
    title="<?= esc($display ?? '') ?>"
    type="text"
    <?= is_required($validation ?? '') ? 'required' : '' ?>
    class="form-control"
    placeholder="<?= esc($meta['placeholder'] ?? $placeholder ?? '') ?>"
    value="<?= esc($value ?? '') ?>"
>
