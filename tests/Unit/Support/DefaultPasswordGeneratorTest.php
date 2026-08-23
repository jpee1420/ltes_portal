<?php

use App\Support\DefaultPasswordGenerator;
use Illuminate\Support\Carbon;

test('generates default password for student from string date and LRN', function () {
    $password = DefaultPasswordGenerator::forStudent('2015-11-14', '101010101001');

    // Expected: MMDDYYYY + last 4 digits of LRN
    // 11142015 + 1001 = 111420151001
    expect($password)->toBe('111420151001');
});

test('generates default password from Carbon instance', function () {
    $dob = Carbon::parse('2016-05-08');
    $password = DefaultPasswordGenerator::forStudent($dob, '123456789012');

    // 05082016 + 9012 = 050820169012
    expect($password)->toBe('050820169012');
});

test('generates default password for staff from birth date and employee number', function () {
    $password = DefaultPasswordGenerator::forStaff('1988-03-25', '2000001');

    // 03251988 + 0001 = 032519880001
    expect($password)->toBe('032519880001');
});

test('handles short credentials gracefully by taking entire credential if under 4 chars', function () {
    $password = DefaultPasswordGenerator::forStudent('2015-01-02', '12');

    // 01022015 + 12 = 0102201512
    expect($password)->toBe('0102201512');
});

test('throws invalid argument exception when birth date is empty or invalid', function () {
    expect(fn () => DefaultPasswordGenerator::forStudent('', '101010101001'))
        ->toThrow(InvalidArgumentException::class);
});
