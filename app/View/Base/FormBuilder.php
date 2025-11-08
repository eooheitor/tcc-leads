<?php

namespace App\View\Base;

class FormBuilder
{
    protected array $fields = [];
    protected array $disabledFields = [];
    protected string $title = '';
    protected string $method = 'POST';
    protected string $routeForm = '';
    protected array $data = [];
    protected array $fieldDefinitions = [];
    protected bool $multipart = false;
    protected bool $isEditMode = false;

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function getFieldDefinitions(): array
    {
        return $this->fieldDefinitions;
    }

    public function setRouteForm(string $routeForm): self
    {
        $this->routeForm = $routeForm;
        return $this;
    }

    public function withData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function disabledOnEdit(array $fields): self
    {
        $this->disabledFields = $fields;
        return $this;
    }

    public function setEditMode(bool $isEdit): self
    {
        $this->isEditMode = $isEdit;
        return $this;
    }

    protected function isDisabled(string $name): bool
    {
        return $this->isEditMode && in_array($name, $this->disabledFields, true);
    }

    public function getData(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRouteForm(): string
    {
        return $this->routeForm;
    }

    public function getMultipart(): bool
    {
        return $this->multipart;
    }

    public function setMultipart(bool $enable = true): self
    {
        $this->multipart = $enable;
        return $this;
    }

    public function text(string $name, string $label, $value = '', $placeholder = ''): self
    {
        $disabled = $this->isDisabled($name);
        $this->fields[] = view('components.form.text', compact('name', 'label', 'value', 'placeholder', 'disabled'))->render();
        return $this;
    }

    public function select(
        string $name,
        string $label,
        array $options = [],
        $selected = null,
        bool $disabled = false
    ): self {
        $disabled = $disabled || $this->isDisabled($name);

        $this->fields[] = view('components.form.select', compact(
            'name',
            'label',
            'options',
            'selected',
            'disabled'
        ))->render();

        return $this;
    }

    public function checkbox(string $name, string $label, bool $checked = false): self
    {
        $this->fields[] = view('components.form.checkbox', compact('name', 'label', 'checked'))->render();
        return $this;
    }

    public function color(string $name, string $label, $value = '#000000'): self
    {
        $this->fields[] = view('components.form.color', compact('name', 'label', 'value'))->render();
        return $this;
    }

    public function submit(string $label = 'Salvar'): self
    {
        $this->fields[] = view('components.form.submit', compact('label'))->render();
        return $this;
    }

    public function textarea(string $name, string $label, $value = ''): self
    {
        $this->fields[] = view('components.form.textarea', compact('name', 'label', 'value'))->render();
        return $this;
    }

    public function phone(string $name, string $label, $value = '', string $placeholder = ''): self
    {
        $disabled = method_exists($this, 'isDisabled') ? $this->isDisabled($name) : false;
        $this->fields[] = view('components.form.phone', compact('name', 'label', 'value', 'placeholder', 'disabled'))->render();
        return $this;
    }

    public function file(string $name, string $label, string $accept = ''): self
    {
        $this->fields[] = view('components.form.file', compact('name', 'label', 'accept'))->render();
        return $this;
    }

    public function fileMultiple(string $name, string $label, string $accept = ''): self
    {
        $this->fields[] = view('components.form.file_multiple', compact('name', 'label', 'accept'))->render();
        return $this;
    }

    public function date(string $name, string $label, $value = ''): self
    {
        $disabled = $this->isDisabled($name);
        $this->fields[] = view('components.form.date', compact('name', 'label', 'value', 'disabled'))->render();
        return $this;
    }

    public function money(string $name, string $label, $value = '', string $placeholder = ''): self
    {
        $disabled = $this->isDisabled($name);
        $this->fields[] = view('components.form.money', compact('name', 'label', 'value', 'placeholder', 'disabled'))->render();
        return $this;
    }

    public function render(): string
    {
        return implode("\n", $this->fields);
    }
}
