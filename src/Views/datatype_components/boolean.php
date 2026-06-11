<div class="form-check form-switch">
    <input type="hidden" name="data[<?= esc($name ?? '') ?>]" value="0">

    <input
        id="<?= esc($id ?? '') ?>"
        name="data[<?= esc($name ?? '') ?>]"
        title="<?= esc($display ?? '') ?>"
        type="checkbox"
        class="form-check-input"
        value="1"
        <?= ($value ?? false) ? 'checked' : '' ?>
    >
</div>
