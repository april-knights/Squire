<?php

namespace Database\Factories\Model;

use Illuminate\Database\Eloquent\Factories\Factory;

trait FactoryHasActive
{
    public function inactive() {
        /** @var Factory $this */
        $this->state(fn (array $attrs) => ['activeflg' => 0]);
    }

    public function deleted() {
        /** @var Factory $this */
        $this->state(fn (array $attrs) => ['delflg' => 1]);
    }
}
