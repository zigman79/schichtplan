<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TimeDisplay extends Component
{

    /**
     * the time to display
     */
    public $time;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($time)
    {
        $this->time = $time;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return function (array $data) {
            return '<span class="time-display">' . intdiv($data['time'], 60) . ':' . ($data['time'] % 60 < 10 ? '0' . $data['time'] % 60 : $data['time'] % 60) . '</span>';
        };
    }
}
