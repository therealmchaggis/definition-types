<textarea
    id="<?= esc($id ?? '') ?>"
    name="data[<?= esc($name ?? '') ?>]"
    title="<?= esc($display ?? '') ?>"
    <?= is_required($validation ?? '') ? 'required' : '' ?>
    class="form-control <?= (! empty($meta['richtext']) ? 'richtext' : '') ?>"
    placeholder="<?= esc($meta['placeholder'] ?? $placeholder ?? '') ?>"
    rows="<?= $meta['rows'] ?? 5 ?>"
><?= esc($value ?? '') ?></textarea>
