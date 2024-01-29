<?php

namespace App\View\Components\Form;

use Illuminate\View\Component;

class input extends Component
{
    public $attr;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(array $attr)
    {
        $this->attr = $attr;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.form.input');
    }
}
