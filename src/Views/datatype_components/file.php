<input
    id="<?= esc($id ?? '') ?>"
    name="<?= esc($name ?? '') ?>"
    title="<?= esc($display ?? '') ?>"
    type="file"
    <?= is_required($validation ?? '') ? 'required' : '' ?>
    class="form-control"
    placeholder="<?= esc($meta['placeholder'] ?? '') ?>"
    value="<?= esc($value ?? '') ?>"
>

<div id="uploadBox-<?= esc($name ?? '') ?>">
    <button id="uploadBtn-<?= esc($name ?? '') ?>" type="button" class="btn btn-primary">Choose File</button>
    <div id="progressBox-<?= esc($name ?? '') ?>" style="display:none;">
        <div id="progressBar-<?= esc($name ?? '') ?>" style="width:0%;background:#4caf50;height:20px;"></div>
    </div>
    <div id="msgBox"></div>
</div>
