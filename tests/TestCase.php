<?php

namespace Tests;

use Faker\Provider\pt_BR\Company;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker->addProvider(new Company($this->faker));
    }
}
