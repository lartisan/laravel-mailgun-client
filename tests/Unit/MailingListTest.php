<?php

use Lartisan\MailgunClient\ValueObjects\MailingList;

describe('MailingList', function () {

    it('creates an instance from an array', function (): void {
        $data = [
            'access_level' => 'readonly',
            'address' => 'newsletter@example.com',
            'created_at' => '2024-01-01T00:00:00Z',
            'description' => 'Main newsletter list',
            'members_count' => 150,
            'name' => 'Newsletter',
            'reply_preference' => 'list',
        ];

        $list = MailingList::from($data);

        expect($list->access_level)->toBe('readonly')
            ->and($list->address)->toBe('newsletter@example.com')
            ->and($list->created_at)->toBe('2024-01-01T00:00:00Z')
            ->and($list->description)->toBe('Main newsletter list')
            ->and($list->members_count)->toBe(150)
            ->and($list->name)->toBe('Newsletter')
            ->and($list->reply_preference)->toBe('list');
    });

    it('accepts null for members_count', function (): void {
        $data = [
            'access_level' => 'readonly',
            'address' => 'list@example.com',
            'created_at' => '2024-01-01T00:00:00Z',
            'description' => '',
            'members_count' => null,
            'name' => 'Empty List',
            'reply_preference' => 'sender',
        ];

        $list = MailingList::from($data);

        expect($list->members_count)->toBeNull();
    });

    it('converts to an array correctly', function (): void {
        $data = [
            'access_level' => 'members',
            'address' => 'members@example.com',
            'created_at' => '2024-06-15T12:00:00Z',
            'description' => 'Members only',
            'members_count' => 42,
            'name' => 'Members',
            'reply_preference' => 'sender',
        ];

        $list = MailingList::from($data);

        expect($list->toArray())->toBe($data);
    });

    it('creates a collection from multiple items', function (): void {
        $items = [
            [
                'access_level' => 'readonly',
                'address' => 'list1@example.com',
                'created_at' => '2024-01-01T00:00:00Z',
                'description' => 'List one',
                'members_count' => 10,
                'name' => 'List One',
                'reply_preference' => 'list',
            ],
            [
                'access_level' => 'everyone',
                'address' => 'list2@example.com',
                'created_at' => '2024-02-01T00:00:00Z',
                'description' => 'List two',
                'members_count' => 20,
                'name' => 'List Two',
                'reply_preference' => 'sender',
            ],
        ];

        $collection = MailingList::toCollection($items);

        expect($collection)->toHaveCount(2)
            ->and($collection->first())->toBeInstanceOf(MailingList::class)
            ->and($collection->first()->address)->toBe('list1@example.com')
            ->and($collection->last()->address)->toBe('list2@example.com');
    });

    it('returns an empty collection for an empty items array', function (): void {
        $collection = MailingList::toCollection([]);

        expect($collection)->toHaveCount(0);
    });

});
