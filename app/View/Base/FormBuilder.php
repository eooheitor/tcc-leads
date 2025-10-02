<?php

namespace App\View\Base;

class FormBuilder
{
    protected array $fields = [];
    protected string $title = '';
    protected string $method = 'POST';
    protected string $routeForm = '';
    protected array $data = [];
    protected array $fieldDefinitions = [];

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

    public function text(string $name, string $label, $value = ''): self
    {
        $this->fields[] = view('components.form.text', compact('name', 'label', 'value'))->render();
        return $this;
    }

    public function select(string $name, string $label, array $options = [], $selected = null): self
    {
        $this->fields[] = view('components.form.select', compact('name', 'label', 'options', 'selected'))->render();
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

    public function file(string $name, string $label, ?string $currentFile = null): self
    {
        $this->fields[] = view('components.form.file', compact('name', 'label', 'currentFile'))->render();
        return $this;
    }

    public function render(): string
    {
        return implode("\n", $this->fields);
    }
}
