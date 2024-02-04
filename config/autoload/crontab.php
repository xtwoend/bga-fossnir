<?php declare(strict_types=1);

use Hyperf\Crontab\Crontab;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

return [
    'enable' => true,
    'crontab' => [
        
        (new Crontab())->setType('command')->setName('FossnirCSVParser_01')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 1,
            '--disable-event-dispatcher' => true,
        ]),

        (new Crontab())->setType('command')->setName('FossnirCSVParser_02')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 2,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_03')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 3,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_04')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 4,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_05')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 5,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_06')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 6,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_07')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 7,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_08')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 8,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_09')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 9,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_10')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 10,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_11')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 11,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_12')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 12,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_13')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 13,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_14')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 14,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('FossnirCSVParser_15')->setRule('*/30 * * * *')->setCallback([
            'command' => 'fossnir:parse',
            'mill_id' => 15,
            '--disable-event-dispatcher' => true,
        ]),

        // read
        (new Crontab())->setType('command')->setName('ReadFileFossnir_01')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 1,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_02')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 2,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_03')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 3,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_04')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 4,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_05')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 5,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_06')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 6,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_07')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 7,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_08')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 8,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_09')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 9,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_10')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 10,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_11')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 11,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_12')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 12,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_13')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 13,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_14')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 14,
            '--disable-event-dispatcher' => true,
        ]),
        (new Crontab())->setType('command')->setName('ReadFileFossnir_15')->setRule('*/60 * * * *')->setCallback([
            'command' => 'fossnir:read-file',
            'mill_id' => 15,
            '--disable-event-dispatcher' => true,
        ]),

        // send to mqtt
        // (new Crontab())->setType('command')->setName('sendLatestData_01')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 1,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_02')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 2,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_03')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 3,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_04')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 4,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_05')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 5,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_06')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 6,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_07')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 7,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_08')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 8,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_09')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 9,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_10')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 10,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_11')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 11,
        //     '--disable-event-dispatcher' => true,
        // ]),
        (new Crontab())->setType('command')->setName('sendLatestData_12')->setRule('* * * * *')->setCallback([
            'command' => 'fossnir:send',
            'mill_id' => 12,
            '--disable-event-dispatcher' => true,
        ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_13')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 13,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_14')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 14,
        //     '--disable-event-dispatcher' => true,
        // ]),
        // (new Crontab())->setType('command')->setName('sendLatestData_15')->setRule('* * * * *')->setCallback([
        //     'command' => 'fossnir:send',
        //     'mill_id' => 15,
        //     '--disable-event-dispatcher' => true,
        // ]),
    ],
];
