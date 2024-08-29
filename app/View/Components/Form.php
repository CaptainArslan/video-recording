<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Form extends Component
{
    public $fields;

    public $action;

    public $method;

    public $enctype;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(array $fields, string $action, string $method, ?bool $enctype = false)
    {
        $this->fields = $fields;
        $this->action = $action;
        $this->method = $method;
        $this->enctype = $enctype;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.form');
    }
}
