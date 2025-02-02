<?php

namespace Vanacode\Support\Traits;

use Illuminate\Support\Facades\Blade;

trait BladeTrait
{
    public function renderLink(?string $url, ?string $label = null, array $data = []): string
    {
        if (! $url || ! $label) {
            return '';
        }
        $data['href'] = $url;
        $data['label'] = $label;

        return $this->renderComponent('link', $data);
    }

    public function renderComponent(string $name, array $data = []): string
    {
        $rowData = [];
        $attributes = '';
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $attributes .= ' '.$value;

                continue;
            }
            if (is_string($value)) {
                $attributes .= sprintf(' %s="%s"', $key, $value);

                continue;
            }
            if (is_array($value) || is_object($value)) {
                $rowData[$key] = $value;
                $attributes .= sprintf(' :%s="$%s"', $key, $key);
            }
        }
        $content = sprintf('<x-%s%s/>', $name, $attributes);

        return Blade::render($content, $rowData);
    }
}
