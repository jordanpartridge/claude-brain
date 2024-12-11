<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeValid', function (bool $valid) {
    if ($valid) {
        return $this->toBeInstanceOf(\App\Models\Entity::class);
    }
    
    return $this->toThrow(\Illuminate\Validation\ValidationException::class);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create a test graph structure with the given depth
 */
function createGraphStructure($rootEntity, int $depth): void
{
    if ($depth <= 0) {
        return;
    }

    $children = \App\Models\Entity::factory(2)->create();
    
    foreach ($children as $child) {
        $rootEntity->relateTo($child, 'contains');
        createGraphStructure($child, $depth - 1);
    }
}
