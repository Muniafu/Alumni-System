<?php

namespace App\Charts;

class ColumnChart
{
    public $title;
    public $animated;
    public $columns = [];
    public $colors = [];
    public $clickEventName;

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setAnimated($animated)
    {
        $this->animated = $animated;
        return $this;
    }

    public function withOnColumnClickEventName($eventName)
    {
        $this->clickEventName = $eventName;
        return $this;
    }

    public function addColumn($label, $value, $color)
    {
        $this->columns[] = [
            'label' => $label,
            'value' => $value,
            'color' => $color
        ];
        return $this;
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'animated' => $this->animated,
            'columns' => $this->columns,
            'clickEventName' => $this->clickEventName
        ];
    }
}