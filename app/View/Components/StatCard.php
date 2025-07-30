<?php

namespace App\View\Components;

use Illuminate\View\Component;

class StatCard extends Component
{
    public $title;
    public $icon;
    public $value;
    public $description;

    public function __construct($title, $icon = null, $value = null, $description = null)
    {
        $this->title = $title;
        $this->icon = $icon;
        $this->value = $value;
        $this->description = $description;
    }

    public function render()
    {
        return view('components.stat-card');
    }
} 