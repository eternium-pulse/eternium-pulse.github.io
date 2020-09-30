<?php

namespace Eternium\Event;

return [
    Season::create(1)
        ->withMages('5d24a1b4ec2a60555c62addb')
        ->withWarriors('5d24a1b5ec2a60555c62ade0')
        ->withBountyHunters('5d24a1b7ec2a60555c62ade3'),

    Season::create(2)
        ->withMages('5dc1bd9aec2a605db2e06bcc')
        ->withWarriors('5dc1bd9bec2a605db2e06bd0')
        ->withBountyHunters('5dc1bd9cec2a605db2e06bd1'),

    Season::create(3)
        ->withMages('5e6cf9abec2a6078be51c20c')
        ->withWarriors('5e6cf9acec2a6078be51c20d')
        ->withBountyHunters('5e6cf9ad76f55b6deb838e40'),

    Season::create(4)
        ->withMages('5f12cfa04d28152e930c68b1')
        ->withWarriors('5f12cfa14d28152e930c68b2')
        ->withBountyHunters('5f12cfa24d28152e930c68b3')
        ->endOn('2020-10-01T19:00'),

    Anb::create(
        1,
        League::createBronze()
            ->withMages('5c66b27276f55b70c12c0859')
            ->withWarriors('5c66b273ec2a6039708d607d')
            ->withBountyHunters('5c66b27476f55b70c12c085f'),
        League::createSilver()
            ->withMages('5c7553e076f55b70c138b79c')
            ->withWarriors('5c7553e1ec2a6039709a6e90')
            ->withBountyHunters('5c7553e2ec2a6039709a6e91'),
        League::createGold()
            ->withMages('5c8a4534ec2a604c09436502')
            ->withWarriors('5c8a4535ec2a604c09436503')
            ->withBountyHunters('5c8a4536ec2a604c09436506'),
    ),

    Anb::create(
        2,
        League::createBronze()
            ->withMages('5cd43d63ec2a607f5e725769')
            ->withWarriors('5cd43d65ec2a607f5e72576a')
            ->withBountyHunters('5cd43d66ec2a607f5e72576d'),
        League::createSilver()
            ->withMages('5ce558b5ec2a60555c02f7c9')
            ->withWarriors('5ce558b7ec2a60555c02f7ce')
            ->withBountyHunters('5ce558b8ec2a60555c02f7d4'),
        League::createGold()
            ->withMages('5cfa6375ec2a60555c2a4c8a')
            ->withWarriors('5cfa6376ec2a60555c2a4c8d')
            ->withBountyHunters('5cfa6377ec2a60555c2a4c8e'),
    ),

    Anb::create(
        3,
        League::createBronze()
            ->withMages('5d56e3f8ec2a600b30edfab4')
            ->withWarriors('5d56e3f9ec2a600b30edfab9')
            ->withBountyHunters('5d56e3faec2a600b30edfac4'),
        League::createSilver()
            ->withMages('5da5a78fec2a60109c55552b')
            ->withWarriors('5da5a790ec2a60109c55552c')
            ->withBountyHunters('5da5a791ec2a60109c55552d'),
        League::createGold()
            ->withMages('5dcee304ec2a605db2f607a4')
            ->withWarriors('5dcee305ec2a605db2f607a5')
            ->withBountyHunters('5dcee306ec2a605db2f607a6'),
    ),

    Anb::create(
        4,
        League::createBronze()
            ->withMages('5e87555376f55b1a5ab4630a')
            ->withWarriors('5e87555576f55b1a5ab4630d')
            ->withBountyHunters('5e87555676f55b1a5ab4630e'),
        League::createSilver()
            ->withMages('5e9abe5c8c7cf70e1b6910b4')
            ->withWarriors('5e9abe5d8c7cf70e1b6910b5')
            ->withBountyHunters('5e9abe5e4d281509fcf62d0c'),
        League::createGold()
            ->withMages('5ead46494d2815023de6e0f7')
            ->withWarriors('5ead464a4d2815023de6e0f8')
            ->withBountyHunters('5ead464b8c7cf757adbb5576'),
    ),

    Anb::create(
        5,
        League::createBronze()
            ->withMages('5ed2680f4d281531c99d4b61')
            ->withWarriors('5ed268108c7cf757adcd5757')
            ->withBountyHunters('5ed268118c7cf757adcd5758'),
        League::createSilver()
            ->withMages('5ee4770c4d281531c91cad6d')
            ->withWarriors('5ee4770d8c7cf7080260c331')
            ->withBountyHunters('5ee4770e8c7cf7080260c332'),
        League::createGold()
            ->withMages('5f25c1ea8c7cf733bc232552')
            ->withWarriors('5f25c1ec8c7cf733bc232553')
            ->withBountyHunters('5f25c1ed8c7cf733bc232554'),
    ),

    Anb::create(
        6,
        League::createBronze()
            ->withMages('5f35a1894d28155b896d7c6a')
            ->withWarriors('5f35a18a4d28155b896d7c73')
            ->withBountyHunters('5f35a18b8c7cf73d2d553e71')
            ->startOn('2020-08-15T19:00')
            ->endOn('2020-08-23T19:00'),
        League::createSilver()
            ->withMages('5f52609f4d28152c2bacbd43')
            ->withWarriors('5f5260a14d28152c2bacbd48')
            ->withBountyHunters('5f5260a24d28152c2bacbd4b')
            ->startOn('2020-09-05T19:00')
            ->endOn('2020-09-13T19:00'),
        League::createGold()
            ->withMages('5f64c6934d28153ae45a252f')
            ->withWarriors('5f64c6944d28153ae45a253c')
            ->withBountyHunters('5f64c6968c7cf76d90955324')
            ->startOn('2020-09-19T19:00')
            ->endOn('2020-09-27T19:00'),
    ),
];
