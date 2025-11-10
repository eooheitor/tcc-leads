<?php

namespace App\View\Base;

use Carbon\Carbon;
use Illuminate\Support\HtmlString;

class GridBuilder
{
    protected string $title = '';
    protected string $formView = '';
    protected string $delete = '';
    protected string $modelName = '';
    protected array $formData = [];
    protected array $columns = [];
    protected $rows;
    protected $routeName;
    protected $routeCreate;
    protected $routeEdit;
    protected array $actionButtons = [];

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function setRouteCreate($routeCreate)
    {
        $this->routeCreate = $routeCreate;
        return $this;
    }

    public function getRouteCreate()
    {
        return $this->routeCreate;
    }

    public function setRouteEdit($routeEdit)
    {
        $this->routeEdit = $routeEdit;
        return $this;
    }

    public function addActionButton(
        string $routeName,
        ?string $icon = null,
        string $classes = 'bg-gray-600 text-white hover:bg-gray-700',
        ?string $label = null
    ): self {
        $this->actionButtons[] = [
            'route'   => $routeName,
            'icon'    => $icon,
            'classes' => $classes,
            'label'   => $label,
        ];

        return $this;
    }

    public function getActionButtons(): array
    {
        return $this->actionButtons;
    }

    public function getRouteEdit()
    {
        return $this->routeEdit;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setFormView(string $formView): self
    {
        $this->formView = $formView;
        return $this;
    }

    public function setFormData(array $formData): self
    {
        $this->formData = $formData;
        return $this;
    }

    public function setRouteDelete(string $delete): self
    {
        $this->delete = $delete;
        return $this;
    }

    public function setModelName(string $modelName): self
    {
        $this->modelName = $modelName;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFormView(): string
    {
        return $this->formView;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getRouteDelete(): string
    {
        return $this->delete;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function column(string $key, string $label, $callback = null): self
    {
        $this->columns[] = compact('key', 'label', 'callback');
        return $this;
    }

    public function columnArea(string $key, string $label, int $maxChars = 0): self
    {
        $callback = function ($row) use ($key, $maxChars) {
            $html = (string) data_get($row, $key, '');
            $text = preg_replace('/<(br|BR)\s*\/?>/i', "\n", $html);
            $text = preg_replace('/<\/(p|div|li|h[1-6])>/i', "\n", $text);
            $text = strip_tags($text);
            $text = preg_replace("/\n{3,}/", "\n\n", $text);

            if ($maxChars > 0 && mb_strlen($text) > $maxChars) {
                $text = mb_substr($text, 0, $maxChars) . '…';
            }

            return new \Illuminate\Support\HtmlString(nl2br(e($text)));
        };

        return $this->column($key, $label, $callback);
    }

    public function textTruncateColumn(
        string $key,
        string $label,
        array $map,
        int $maxLen = 40,
        string $fallback = '—',
        bool $showTooltip = true
    ): self {
        $callback = function ($row) use ($key, $map, $maxLen, $fallback, $showTooltip) {
            $id = (string) data_get($row, $key);

            if ($id === '' || !array_key_exists($id, $map)) {
                return new HtmlString('<span class="text-gray-400 text-sm">' . $fallback . '</span>');
            }

            $name = trim((string) $map[$id]);

            // Truncar (respeita multibyte)
            if ($maxLen > 0 && mb_strlen($name) > $maxLen) {
                $short = mb_substr($name, 0, $maxLen) . '…';
                $title = $showTooltip ? ' title="' . e($name) . '"' : '';
                return new HtmlString('<span' . $title . '>' . e($short) . '</span>');
            }

            return e($name);
        };

        return $this->column($key, $label, $callback);
    }

    public function imageColumn(string $key, string $label, int $size = 60): self
    {
        $callback = function ($row) use ($key, $size) {
            $value = data_get($row, $key);

            if (!$value) {
                return '<span class="text-gray-400 text-sm">Sem imagem</span>';
            }

            return sprintf(
                '<img src="%s" alt="%s" style="width:%dpx; height:%dpx; object-fit:cover; border-radius:8px;" />',
                e($value),
                e($row->name ?? 'Imagem'),
                $size,
                $size
            );
        };

        return $this->column($key, $label, $callback);
    }

    public function badgeColumn(string $key, string $label, array $colorsByValue = [], string $defaultColor = '#6b7280'): self
    {
        $callback = function ($row) use ($key, $colorsByValue, $defaultColor) {
            $val = (string) data_get($row, $key, '');
            if ($val === '') return '<span class="text-gray-400 text-sm">—</span>';

            $color = $colorsByValue[$val] ?? $defaultColor;

            return sprintf(
                '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:%s20;color:%s;border:1px solid %s33">%s</span>',
                $color,
                $color,
                $color,
                e($val)
            );
        };

        return $this->column($key, $label, $callback);
    }

    public function statusColumn(string $key = 'status', string $label = 'Status'): self
    {
        return $this->badgeColumn($key, $label, [
            'ACTIVE'   => '#16a34a',
            'PAUSED'   => '#d97706',
            'ARCHIVED' => '#6b7280',
            'DELETED'  => '#dc2626',
        ]);
    }

    public function enumColumn(string $key, string $label, array $map, string $fallback = '—'): self
    {
        $callback = function ($row) use ($key, $map, $fallback) {
            $raw = (string) data_get($row, $key, '');
            if ($raw === '' || $raw === 'UNDEFINED' || $raw === 'UNKNOWN') {
                return new HtmlString('<span class="text-gray-400 text-sm">' . $fallback . '</span>');
            }
            $text = $map[$raw] ?? $fallback;
            return e($text);
        };
        return $this->column($key, $label, $callback);
    }

    public function dateColumn(string $key, string $label, string $format = 'd/m/Y', ?string $tz = null): self
    {
        $callback = function ($row) use ($key, $format, $tz) {
            $val = data_get($row, $key);
            if (!$val) {
                return new HtmlString('<span class="text-gray-400 text-sm">—</span>');
            }
            try {
                $c = Carbon::parse($val);
                if ($tz) $c->setTimezone($tz);
                return e($c->format($format));
            } catch (\Throwable $e) {
                // Se não deu pra parsear, mostra “—” com o valor bruto no title
                $escaped = e((string) $val);
                return new HtmlString('<span class="text-gray-400 text-sm" title="' . $escaped . '">—</span>');
            }
        };
        return $this->column($key, $label, $callback);
    }

    public function moneyCentsColumn(string $key, string $label): self
    {
        $callback = function ($row) use ($key) {
            $val = data_get($row, $key);
            if ($val === null || $val === '' || $val === 0 || $val === '0') {
                return new HtmlString('<span class="text-gray-400 text-sm">—</span>');
            }
            if (is_numeric($val)) {
                $brl = number_format(((int) $val) / 100, 2, ',', '.');
                return 'R$ ' . $brl;
            }
            return e((string) $val);
        };
        return $this->column($key, $label, $callback);
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getFormCreateUrl(): string
    {
        return route($this->getRouteName() . '.form.create');
    }

    public function getFormEditUrl($id): string
    {
        return route($this->getRouteName() . '.form.edit', $id);
    }

    public function getRouteName(): string
    {
        return $this->routeName ?? 'undefined';
    }

    public function setRouteName(string $name): self
    {
        $this->routeName = $name;
        return $this;
    }

    public function render(array $extra = [])
    {
        return view('components.grid.grid', array_merge([
            'grid'    => $this,
            'columns' => $this->columns,
            'rows'    => $this->rows,
        ], $extra))->render();
    }
}
