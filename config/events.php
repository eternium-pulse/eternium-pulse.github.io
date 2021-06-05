<?php

namespace Eternium\Event;

return [
    Season::create(1, 'Summer 2019')
        ->withMages('5d24a1b4ec2a60555c62addb')
        ->withWarriors('5d24a1b5ec2a60555c62ade0')
        ->withBountyHunters('5d24a1b7ec2a60555c62ade3'),

    Season::create(2, 'Winter 2019')
        ->withMages('5dc1bd9aec2a605db2e06bcc')
        ->withWarriors('5dc1bd9bec2a605db2e06bd0')
        ->withBountyHunters('5dc1bd9cec2a605db2e06bd1'),

    Season::create(3, 'Spring 2020')
        ->withMages('5e6cf9abec2a6078be51c20c')
        ->withWarriors('5e6cf9acec2a6078be51c20d')
        ->withBountyHunters('5e6cf9ad76f55b6deb838e40'),

    Season::create(4, 'Summer 2020')
        ->withMages('5f12cfa04d28152e930c68b1')
        ->withWarriors('5f12cfa14d28152e930c68b2')
        ->withBountyHunters('5f12cfa24d28152e930c68b3')
        ->endsOn('2020-10-01T19:00'),

    Season::create(5, 'Winter 2020')
        ->withMages('5fe99e7a4d2815223b77ba5c')
        ->withWarriors('5fe99e7c8c7cf70b431a7f64')
        ->withBountyHunters('5fe99e7d8c7cf70b431a7f68')
        ->startsOn('2020-12-28T19:00')
        ->endsOn('2021-03-18T19:00'),

    Season::create(6, 'Spring 2021')
        ->withMages('6054b7288c7cf71171671dc0')
        ->withWarriors('6054b7294d2815383bcc574f')
        ->withBountyHunters('6054b72a4d2815383bcc5755')
        ->startsOn('2021-03-19T19:00')
        ->endsIn('P73D'),

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
            ->withBountyHunters('5f25c1ed8c7cf733bc232554')
            ->startsOn('2020-08-01T19:00')
            ->endsOn('2020-08-09T19:00'),
    ),

    Anb::create(
        6,
        League::createBronze()
            ->withMages('5f35a1894d28155b896d7c6a')
            ->withWarriors('5f35a18a4d28155b896d7c73')
            ->withBountyHunters('5f35a18b8c7cf73d2d553e71')
            ->startsOn('2020-08-15T19:00')
            ->endsOn('2020-08-23T19:00'),
        League::createSilver()
            ->withMages('5f52609f4d28152c2bacbd43')
            ->withWarriors('5f5260a14d28152c2bacbd48')
            ->withBountyHunters('5f5260a24d28152c2bacbd4b')
            ->startsOn('2020-09-05T19:00')
            ->endsOn('2020-09-13T19:00'),
        League::createGold()
            ->withMages('5f64c6934d28153ae45a252f')
            ->withWarriors('5f64c6944d28153ae45a253c')
            ->withBountyHunters('5f64c6968c7cf76d90955324')
            ->startsOn('2020-09-19T19:00')
            ->endsOn('2020-09-27T19:00'),
    ),

    Anb::create(
        7,
        League::createBronze()
            ->withMages('5f8061074d281532ef64914b')
            ->withWarriors('5f8061088c7cf71b88bfbc57')
            ->withBountyHunters('5f8061094d281532ef649161')
            ->startsOn('2020-10-09T19:00')
            ->endsOn('2020-10-18T19:00'),
        League::createSilver()
            ->withMages('5f92de1a8c7cf71b885774ae')
            ->withWarriors('5f92de1b8c7cf71b885774bb')
            ->withBountyHunters('5f92de1c4d28152c65135d70')
            ->startsOn('2020-10-24T19:00')
            ->endsOn('2020-11-02T19:00'),
        League::createGold()
            ->withMages('5fe99f294d2815223b77bcdf')
            ->withWarriors('5fe99f2a8c7cf70b431a80b1')
            ->withBountyHunters('5fe99f2b8c7cf70b431a80b6')
            ->startsOn('2020-12-28T19:00')
            ->endsOn('2021-01-10T19:00'),
    ),

    Anb::create(
        8,
        League::createBronze()
            ->withMages('600178584d281509f648259e')
            ->withWarriors('600178594d281509f64825ab')
            ->withBountyHunters('6001785a8c7cf70b43dd44d7')
            ->startsOn('2021-01-15T17:00')
            ->endsOn('2021-01-25T17:00'),
        League::createSilver()
            ->withMages('60142af28c7cf72865785297')
            ->withWarriors('60142af34d2815677b898b5f')
            ->withBountyHunters('60142af98c7cf72865785307')
            ->startsOn('2021-01-29T17:00')
            ->endsOn('2021-02-08T17:00'),
        League::createGold()
            ->withMages('602ea6468c7cf72865fecd76')
            ->withWarriors('602ea6484d2815677b101e3d')
            ->withBountyHunters('602ea6498c7cf72865fecdb5')
            ->startsOn('2021-02-19T17:00')
            ->endsIn('P10D'),
    ),

    Anb::create(
        9,
        League::createBronze()
            ->withMages('604260da4d2815383b7f0a5c')
            ->withWarriors('604260dc4d2815383b7f0a78')
            ->withBountyHunters('604260dd8c7cf7117118c9a0')
            ->startsOn('2021-03-05T17:00')
            ->endsIn('P10D'),
        League::createSilver()
            ->withMages('60706630f3b8c5126bb13e86')
            ->withWarriors('60706632f3b8c5126bb13e92')
            ->withBountyHunters('60706633f3b8c5126bb13e9d')
            ->startsOn('2021-04-09T17:00')
            ->endsIn('P10D'),
        League::createGold()
            ->withMages('6082fb88c93f431d55875ed3')
            ->withWarriors('6082fb89c93f431d55875ee6')
            ->withBountyHunters('6082fb8af3b8c52fd6445aeb')
            ->startsOn('2021-04-23T17:00')
            ->endsIn('P10D'),
    ),

    Anb::create(
        10,
        League::createBronze()
            ->withMages('60a94376c93f4331b710b0eb')
            ->withWarriors('60a94378c93f4331b710b0f9')
            ->withBountyHunters('60a94379c93f4331b710b111')
            ->startsOn('2021-05-23T17:00')
            ->endsIn('P10D'),
        League::createSilver()
            ->withMages('60ba28d4c93f4334ea0d826e')
            ->withWarriors('60ba28d5f3b8c52fba5611a6')
            ->withBountyHunters('60ba28d7f3b8c52fba5611ac')
            ->startsOn('2021-06-04T17:00')
            ->endsIn('P10D'),
    ),
];
