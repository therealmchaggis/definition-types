<?php
if (! empty($meta['length'])) {
    if (! empty($meta['dp'])) {
        $meta['length'] -= $meta['dp'];
    }
    $placeholder = (! empty($meta['signed']) ? '[-]' : '') . str_repeat('#', $meta['length']);
    if (! empty($meta['dp'])) {
        $placeholder .= '.' . str_repeat('#', $meta['dp']);
    }
}
require __DIR__ . '/text.php';
