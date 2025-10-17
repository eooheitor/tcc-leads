<?php

namespace App\View\Base;

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
        return route($this->getRouteName() . '.form.create'); // ex: cupons.form.create
    }

    public function getFormEditUrl($id): string
    {
        return route($this->getRouteName() . '.form.edit', $id); // ex: cupons.form.edit
    }

    public function getRouteName(): string
    {
        // Retorna o nome base do resource, ex: "cupons"
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
