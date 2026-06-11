<input
    id="<?= esc($id ?? '') ?>"
    name="data[<?= esc($name ?? '') ?>]"
    title="<?= esc($display ?? '') ?>"
    type="time"
    <?= is_required($validation ?? '') ? 'required' : '' ?>
    class="form-control"
    placeholder="<?= esc($meta['placeholder'] ?? '') ?>"
    value="<?= esc($value ?? '') ?>"
>
